<?php
namespace App\Jobs;

use App\Enums\EntityStatus;
use App\Enums\IntegrationBatchStatus;
use App\Enums\StockStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\Collection;
use App\Models\IntegrationBatch;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Seo;
use App\Models\Shop;
use App\Models\Slug;
use App\Services\SeoGenerateService;
use App\Services\SlugGenerateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessBatchProductsJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock('products-batch-import-' . $this->batch->id, 600);

        if (!$lock->get()) {
            return;
        }

        try {
            $this->batch->update([
                'status' => IntegrationBatchStatus::Processing,
                'processed_count' => 0,
                'failed_count' => 0,
                'processed_at' => now(),
            ]);

            $data = json_decode($this->batch->payload, true);

            if (!is_array($data) || empty($data['items'])) {
                $this->failBatch('Empty payload');
                return;
            }

            $items = $data['items'];

            $now = now();

            $categoriesMap = Category::pluck('id', 'external_id');
            $collectionsMap = Collection::pluck('id', 'external_id');
            $promotionsMap = Promotion::pluck('id', 'external_id');

            $processed = 0;
            $failed = 0;

            $rows = [];
            $externalIds = [];

            $productCategories = [];
            $productCollections = [];
            $productPromotions = [];

            collect($items)
                ->chunk(200)
                ->each(function ($chunk) use (
                    &$rows,
                    &$externalIds,
                    &$productCategories,
                    &$productCollections,
                    &$productPromotions,
                    &$processed,
                    &$failed,
                    $categoriesMap,
                    $collectionsMap,
                    $promotionsMap,
                    $now
                ) {
                    [$p, $f, $chunkRows, $chunkIds, $chunkCats, $chunkCols, $chunkPromos] =
                        $this->updateChunk(
                            $chunk->toArray(),
                            $categoriesMap,
                            $collectionsMap,
                            $promotionsMap,
                            $now
                        );

                    $rows = array_merge($rows, $chunkRows);
                    $externalIds = array_merge($externalIds, $chunkIds);

                    $productCategories = array_merge($productCategories, $chunkCats);
                    $productCollections = array_merge($productCollections, $chunkCols);
                    $productPromotions = array_merge($productPromotions, $chunkPromos);

                    $processed += $p;
                    $failed += $f;
                });

            if (empty($rows)) {
                $this->failBatch('No valid products');
                return;
            }

            DB::transaction(function () use (
                $rows,
                $externalIds,
                $productCategories,
                $productCollections,
                $productPromotions
            ) {
                Product::upsert(
                    $rows,
                    ['external_id'],
                    [
                        'group_key',
                        'sku',
                        'title',
                        'description',
                        'short_description',
                        'price',
                        'price_on_sale',
                        'manage_stock',
                        'stock',
                        'stock_status',
                        'status',
                        'published_at',
                        'updated_at',
                    ]
                );

                $productsQuery = Product::query()
                    ->whereIn('external_id', $externalIds);

                $productsGrouped = $productsQuery
                    ->get(['id', 'external_id'])
                    ->keyBy('external_id');

                GenerateEntityMetaJob::dispatch(
                    Product::class,
                    $productsQuery->pluck('id')->all()
                );

                DB::table('product_category')
                    ->whereIn('product_id', $productsGrouped->pluck('id'))
                    ->delete();

                DB::table('product_collection')
                    ->whereIn('product_id', $productsGrouped->pluck('id'))
                    ->delete();

                DB::table('product_promotion')
                    ->whereIn('product_id', $productsGrouped->pluck('id'))
                    ->delete();

                $map = fn ($extId) => $productsGrouped[$extId]->id ?? null;

                $cats = [];
                foreach ($productCategories as $row) {
                    $id = $map($row['external_product_id']);
                    if ($id) {
                        $cats[] = [
                            'product_id' => $id,
                            'category_id' => $row['category_id'],
                        ];
                    }
                }

                $cols = [];
                foreach ($productCollections as $row) {
                    $id = $map($row['external_product_id']);
                    if ($id) {
                        $cols[] = [
                            'product_id' => $id,
                            'collection_id' => $row['collection_id'],
                        ];
                    }
                }

                $promos = [];
                foreach ($productPromotions as $row) {
                    $id = $map($row['external_product_id']);
                    if ($id) {
                        $promos[] = [
                            'product_id' => $id,
                            'promotion_id' => $row['promotion_id'],
                        ];
                    }
                }

                if ($cats) {
                    DB::table('product_category')->insert($cats);
                }

                if ($cols) {
                    DB::table('product_collection')->insert($cols);
                }

                if ($promos) {
                    DB::table('product_promotion')->insert($promos);
                }
            });

            $this->batch->update([
                'status' => IntegrationBatchStatus::Completed,
                'processed_count' => $processed,
                'failed_count' => $failed,
            ]);
        } finally {
            optional($lock)->release();
        }
    }

    private function updateChunk(
        array $items,
              $categoriesMap,
              $collectionsMap,
              $promotionsMap,
              $now
    ): array {
        $processed = 0;
        $failed = 0;

        $rows = [];
        $externalIds = [];

        $productCategories = [];
        $productCollections = [];
        $productPromotions = [];

        foreach ($items as $item) {
            $externalID = $item['id'] ?? '';
            $sku = $item['sku'] ?? '';
            $title = $item['title'] ?? '';

            if ($externalID === '' || $sku === '' || $title === '') {
                $failed++;
                continue;
            }

            $group_key = $item['group_key'] ?? null;
            $description = $item['description'] ?? null;
            $short_description = $item['short_description'] ?? null;
            $price = $item['price'] ?? null;
            $price_on_sale = $item['price_on_sale'] ?? null;

            $manage_stock = (bool) ($item['manage_stock'] ?? false);
            $stock = (int) ($item['stock'] ?? 0);

            $stock_status = $manage_stock && $stock === 0
                ? StockStatus::OutOfStock
                : StockStatus::InStock;

            $rows[] = [
                'external_id' => $externalID,
                'group_key' => $group_key,
                'sku' => $sku,
                'title' => $title,
                'description' => $description,
                'short_description' => $short_description,
                'price' => $price,
                'price_on_sale' => $price_on_sale,
                'manage_stock' => $manage_stock,
                'stock' => $stock,
                'stock_status' => $stock_status,
                'status' => EntityStatus::Published,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $externalIds[] = $externalID;

            foreach (($item['categories'] ?? []) as $extId) {
                if (isset($categoriesMap[$extId])) {
                    $productCategories[] = [
                        'external_product_id' => $externalID,
                        'category_id' => $categoriesMap[$extId],
                    ];
                }
            }

            foreach (($item['collections'] ?? []) as $extId) {
                if (isset($collectionsMap[$extId])) {
                    $productCollections[] = [
                        'external_product_id' => $externalID,
                        'collection_id' => $collectionsMap[$extId],
                    ];
                }
            }

            foreach (($item['promotions'] ?? []) as $extId) {
                if (isset($promotionsMap[$extId])) {
                    $productPromotions[] = [
                        'external_product_id' => $externalID,
                        'promotion_id' => $promotionsMap[$extId],
                    ];
                }
            }

            $processed++;
        }

        return [
            $processed,
            $failed,
            $rows,
            $externalIds,
            $productCategories,
            $productCollections,
            $productPromotions,
        ];
    }

    private function syncSeoAndSlugs(SupportCollection $products): void
    {
        $now = now();

        $slugService = app(SlugGenerateService::class);
        $seoService = app(SeoGenerateService::class);

        $productIds = $products->pluck('id');

        $existingSlugs = Slug::query()
            ->where('entity_type', Product::class)
            ->whereIn('entity_id', $productIds)
            ->pluck('entity_id')
            ->flip()
            ->toArray();

        $existingSeo = Seo::query()
            ->where('entity_type', Product::class)
            ->whereIn('entity_id', $productIds)
            ->pluck('entity_id')
            ->flip()
            ->toArray();

        $allSlugs = Slug::query()
            ->pluck('slug')
            ->toArray();

        $slugRows = [];
        $seoRows = [];

        foreach ($products as $product) {
            if (!isset($existingSlugs[$product->id])) {
                $slug = $slugService->generate(
                    $product->title,
                    $allSlugs
                );

                $allSlugs[] = $slug;

                $slugRows[] = [
                    'slug' => $slug,
                    'entity_type' => Product::class,
                    'entity_id' => $product->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!isset($existingSeo[$product->id])) {
                $seo = $seoService->generateSeo(
                    $product->title,
                    $product->short_description
                        ?: strip_tags($product->description ?? '')
                        ?: $product->title,
                    null
                );

                $seoRows[] = [
                    'entity_type' => Product::class,
                    'entity_id' => $product->id,
                    'title' => $seo['title'],
                    'description' => $seo['description'],
                    'keywords' => $seo['keywords'],
                    'robots' => $seo['robots'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($slugRows) {
            Slug::upsert(
                $slugRows,
                ['entity_type', 'entity_id'],
                ['slug', 'updated_at']
            );
        }

        if ($seoRows) {
            Seo::upsert(
                $seoRows,
                ['entity_type', 'entity_id'],
                ['title', 'description', 'keywords', 'robots', 'updated_at']
            );
        }
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
        ]);
    }
}
