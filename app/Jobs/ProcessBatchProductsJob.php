<?php
namespace App\Jobs;

use App\Enums\EntityStatus;
use App\Enums\IntegrationBatchStatus;
use App\Enums\StockStatus;
use App\Models\Category;
use App\Models\Collection;
use App\Models\IntegrationBatch;
use App\Models\Label;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
            $labelsMap = Label::pluck('id', 'external_id');

            $processed = 0;
            $failed = 0;

            $rows = [];
            $externalIds = [];

            $productCategories = [];
            $productCollections = [];
            $productPromotions = [];
            $productLabels = [];

            $mediaPayload = [];

            $productsGrouped = collect();

            collect($items)
                ->chunk(200)
                ->each(function ($chunk) use (
                    &$rows,
                    &$externalIds,
                    &$productCategories,
                    &$productCollections,
                    &$productPromotions,
                    &$productLabels,
                    &$mediaPayload,
                    &$processed,
                    &$failed,
                    $categoriesMap,
                    $collectionsMap,
                    $promotionsMap,
                    $labelsMap,
                    $now
                ) {
                    [$p, $f, $chunkRows, $chunkIds, $chunkCats, $chunkCols, $chunkPromos, $chunkLabels, $chunkMedia] =
                        $this->updateChunk(
                            $chunk->toArray(),
                            $categoriesMap,
                            $collectionsMap,
                            $promotionsMap,
                            $labelsMap,
                            $now
                        );

                    $rows = array_merge($rows, $chunkRows);
                    $externalIds = array_merge($externalIds, $chunkIds);

                    $productCategories = array_merge($productCategories, $chunkCats);
                    $productCollections = array_merge($productCollections, $chunkCols);
                    $productPromotions = array_merge($productPromotions, $chunkPromos);
                    $productLabels = array_merge($productLabels, $chunkLabels);

                    $mediaPayload = array_merge($mediaPayload, $chunkMedia);

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
                $productPromotions,
                $productLabels,
                &$productsGrouped,
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
                )->onQueue('import');

                DB::table('product_category')
                    ->whereIn('product_id', $productsGrouped->pluck('id'))
                    ->delete();

                DB::table('product_collection')
                    ->whereIn('product_id', $productsGrouped->pluck('id'))
                    ->delete();

                DB::table('product_promotion')
                    ->whereIn('product_id', $productsGrouped->pluck('id'))
                    ->delete();

                DB::table('label_products')
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

                $labels = [];
                foreach ($productLabels as $row) {
                    $id = $map($row['external_product_id']);
                    if ($id) {
                        $labels[] = [
                            'product_id' => $id,
                            'label_id' => $row['label_id'],
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

                if ($labels) {
                    DB::table('label_products')->insert($labels);
                }
            });

            if (!empty($mediaPayload)) {
                $mappedMediaPayload = [];

                foreach ($mediaPayload as $item) {
                    $product = $productsGrouped[$item['id']] ?? null;

                    if (!$product) {
                        continue;
                    }

                    $mappedMediaPayload[] = [
                        'id' => $product->id,
                        'collection' => $item['collection'],
                        'urls' => $item['urls'],
                    ];
                }

                DispatchMediaImportBatchJob::dispatch(
                    Product::class,
                    $mappedMediaPayload,
                    20
                )->onQueue('media');
            }

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
            $labelsMap,
            $now
    ): array {
        $processed = 0;
        $failed = 0;

        $rows = [];
        $externalIds = [];

        $productCategories = [];
        $productCollections = [];
        $productPromotions = [];
        $productLabels = [];

        $mediaPayload = [];

        foreach ($items as $item) {
            $externalID = $item['id'] ?? '';
            $sku = $item['sku'] ?? '';
            $title = $item['title'] ?? '';
            $price = $item['price'] ?? '';

            if ($externalID === '' || $sku === '' || $title === '' || $price === '') {
                $failed++;
                continue;
            }

            $status = EntityStatus::tryFrom($item['status']);

            if (!$status) {
                $status = EntityStatus::Published;
            }

            $rows[] = [
                'external_id' => $externalID,
                'group_key' => $item['group_key'] ?? null,
                'sku' => $sku,
                'title' => $title,
                'description' => $item['description'] ?? null,
                'short_description' => $item['short_description'] ?? null,
                'price' => $price ?? null,
                'price_on_sale' => $item['price_on_sale'] ?? null,
                'manage_stock' => (bool) ($item['manage_stock'] ?? false),
                'stock' => (int) ($item['stock'] ?? 0),
                'stock_status' => ((bool) ($item['manage_stock'] ?? false) && (int) ($item['stock'] ?? 0) === 0)
                    ? StockStatus::OutOfStock
                    : StockStatus::InStock,
                'status' => $status,
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

            foreach (($item['labels'] ?? []) as $extId) {
                if (isset($labelsMap[$extId])) {
                    $productLabels[] = [
                        'external_product_id' => $externalID,
                        'label_id' => $labelsMap[$extId],
                    ];
                }
            }

            if (!empty($item['main_image'])) {
                $mediaPayload[] = [
                    'id' => $externalID,
                    'collection' => 'media',
                    'urls' => [$item['main_image']],
                ];
            }

            if (!empty($item['gallery'])) {
                $mediaPayload[] = [
                    'id' => $externalID,
                    'collection' => 'gallery',
                    'urls' => $item['gallery'],
                ];
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
            $productLabels,
            $mediaPayload,
        ];
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
        ]);
    }
}
