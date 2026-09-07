<?php
namespace App\Jobs;

use App\Enums\EntityStatus;
use App\Enums\ProductTypes;
use App\Enums\StockStatus;
use App\Models\AttributeTerm;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Label;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;

class ProcessBatchProductsJob extends ProcessBatchJob
{
    protected function lockKey(): string
    {
        return 'products-batch-import-' . $this->batch->id;
    }

    protected function chunkSize(): int
    {
        return 200;
    }

    protected function updateChunk(array $items): array
    {
        $now = now();

        $categoriesMap = Category::pluck('id', 'external_id');
        $collectionsMap = Collection::pluck('id', 'external_id');
        $promotionsMap = Promotion::pluck('id', 'external_id');
        $labelsMap = Label::pluck('id', 'external_id');

        $attributeTerms = AttributeTerm::query()
            ->join('attributes', 'attributes.id', '=', 'attribute_terms.attribute_id')
            ->select(
                'attribute_terms.id',
                'attribute_terms.external_id',
                'attribute_terms.attribute_id',
                'attributes.external_id as attribute_external_id',
            )
            ->get()
            ->keyBy(fn ($term) =>
                $term->attribute_external_id . ':' . $term->external_id
            );

        $processed = 0;
        $failed = 0;

        $rows = [];
        $externalIds = [];

        $productCategories = [];
        $productCollections = [];
        $productPromotions = [];
        $productLabels = [];
        $productAttributeTerms = [];

        $mediaPayload = [];

        foreach ($items as $index => $item) {
            $errors = $this->validateItem($item);

            if ($errors) {
                $failed++;

                foreach ($errors as $error) {
                    $this->logError(
                        index: $index,
                        code: $error['code']->value,
                        message: $error['message'],
                        field: $error['field'],
                        externalId: $item['id'] ?? null,
                    );
                }

                continue;
            }

            $externalId = $item['id'];

            $status = EntityStatus::tryFrom($item['status'] ?? '')
                ?? EntityStatus::Published;

            $type = ProductTypes::tryFrom($item['type'] ?? '')
                ?? ProductTypes::SIMPLE;

            $manageStock = (bool) ($item['manage_stock'] ?? false);
            $stock = (int) ($item['stock'] ?? 0);

            $rows[] = [
                'external_id' => $externalId,
                'type' => $type,
                'group_key' => $item['group_key'] ?? null,
                'sku' => $item['sku'],
                'title' => $item['title'],
                'description' => $item['description'] ?? null,
                'short_description' => $item['short_description'] ?? null,
                'price' => $item['price'] ?? null,
                'price_on_sale' => $item['price_on_sale'] ?? null,
                'manage_stock' => $manageStock,
                'stock' => $stock,
                'stock_status' => $manageStock && $stock === 0
                    ? StockStatus::OutOfStock
                    : StockStatus::InStock,
                'status' => $status,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $externalIds[] = $externalId;

            foreach ($item['categories'] ?? [] as $externalCategoryId) {
                if (isset($categoriesMap[$externalCategoryId])) {
                    $productCategories[] = [
                        'external_product_id' => $externalId,
                        'category_id' => $categoriesMap[$externalCategoryId],
                    ];
                }
            }

            foreach ($item['collections'] ?? [] as $externalCollectionId) {
                if (isset($collectionsMap[$externalCollectionId])) {
                    $productCollections[] = [
                        'external_product_id' => $externalId,
                        'collection_id' => $collectionsMap[$externalCollectionId],
                    ];
                }
            }

            foreach ($item['promotions'] ?? [] as $externalPromotionId) {
                if (isset($promotionsMap[$externalPromotionId])) {
                    $productPromotions[] = [
                        'external_product_id' => $externalId,
                        'promotion_id' => $promotionsMap[$externalPromotionId],
                    ];
                }
            }

            foreach ($item['labels'] ?? [] as $externalLabelId) {
                if (isset($labelsMap[$externalLabelId])) {
                    $productLabels[] = [
                        'external_product_id' => $externalId,
                        'label_id' => $labelsMap[$externalLabelId],
                    ];
                }
            }

            foreach ($item['attribute_terms'] ?? [] as $attributeItem) {
                $key = $attributeItem['attribute_id'] . ':' . $attributeItem['id'];

                $term = $attributeTerms->get($key);

                if ($term) {
                    $productAttributeTerms[] = [
                        'external_product_id' => $externalId,
                        'attribute_term_id' => $term->id,
                        'is_variation' => $attributeItem['is_variation'] ?? false,
                    ];
                }
            }

            if (!empty($item['main_image'])) {
                $mediaPayload[] = [
                    'id' => $externalId,
                    'collection' => 'media',
                    'urls' => [$item['main_image']],
                ];
            }

            if (!empty($item['gallery'])) {
                $mediaPayload[] = [
                    'id' => $externalId,
                    'collection' => 'gallery',
                    'urls' => $item['gallery'],
                ];
            }

            $processed++;
        }

        if (!$rows) {
            return [$processed, $failed];
        }

        $this->saveProducts(
            $rows,
            $externalIds,
            $productCategories,
            $productCollections,
            $productPromotions,
            $productLabels,
            $productAttributeTerms,
        );

        $this->dispatchMediaImport(
            $mediaPayload,
            $externalIds
        );

        return [$processed, $failed];
    }

    protected function rules(): array
    {
        return [
            'id' => [
                'required' => true,
            ],
            'sku' => [
                'required' => true,
            ],
            'title' => [
                'required' => true,
            ],
            'price' => [
                'required' => true,
            ],
        ];
    }

    private function saveProducts(
        array $rows,
        array $externalIds,
        array $productCategories,
        array $productCollections,
        array $productPromotions,
        array $productLabels,
        array $productAttributeTerms,
    ): void {
        DB::transaction(function () use (
            $rows,
            $externalIds,
            $productCategories,
            $productCollections,
            $productPromotions,
            $productLabels,
            $productAttributeTerms,
        ) {
            Product::upsert(
                $rows,
                ['external_id'],
                [
                    'type',
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

            $products = Product::query()
                ->whereIn('external_id', $externalIds)
                ->get(['id', 'external_id'])
                ->keyBy('external_id');

            GenerateEntityMetaJob::dispatch(
                Product::class,
                $products->pluck('id')->all()
            )->onQueue('import_1c');

            $productIds = $products->pluck('id');

            DB::table('product_category')
                ->whereIn('product_id', $productIds)
                ->delete();

            DB::table('product_collection')
                ->whereIn('product_id', $productIds)
                ->delete();

            DB::table('product_promotion')
                ->whereIn('product_id', $productIds)
                ->delete();

            DB::table('label_products')
                ->whereIn('product_id', $productIds)
                ->delete();

            DB::table('product_attribute_terms')
                ->whereIn('product_id', $productIds)
                ->delete();

            $this->insertRelations(
                'product_category',
                $productCategories,
                $products,
                'category_id'
            );

            $this->insertRelations(
                'product_collection',
                $productCollections,
                $products,
                'collection_id'
            );

            $this->insertRelations(
                'product_promotion',
                $productPromotions,
                $products,
                'promotion_id'
            );

            $this->insertRelations(
                'label_products',
                $productLabels,
                $products,
                'label_id'
            );

            $this->insertRelations(
                'product_attribute_terms',
                $productAttributeTerms,
                $products,
                'attribute_term_id',
                ['is_variation'],
            );
        });
    }

    private function insertRelations(
        string $table,
        array $rows,
               $products,
        string $relationKey,
        array $extraColumns = [],
    ): void {
        if (!$rows) {
            return;
        }

        $mapped = [];

        foreach ($rows as $row) {
            $productId = $products[$row['external_product_id']]->id ?? null;

            if (!$productId) {
                continue;
            }

            $mappedRow = [
                'product_id' => $productId,
                $relationKey => $row[$relationKey],
            ];

            foreach ($extraColumns as $column) {
                $mappedRow[$column] = $row[$column];
            }

            $mapped[] = $mappedRow;
        }

        if (!$mapped) {
            return;
        }

        $mapped = collect($mapped)
            ->unique(fn ($row) =>
                $row['product_id'] . '-' . $row[$relationKey]
            )
            ->values()
            ->all();

        DB::table($table)->insert($mapped);
    }

    private function dispatchMediaImport(
        array $mediaPayload,
        array $externalIds,
    ): void {
        if (!$mediaPayload) {
            return;
        }

        $products = Product::query()
            ->whereIn('external_id', $externalIds)
            ->get(['id', 'external_id'])
            ->keyBy('external_id');

        $mappedMediaPayload = [];

        foreach ($mediaPayload as $item) {
            $product = $products[$item['id']] ?? null;

            if (!$product) {
                continue;
            }

            $mappedMediaPayload[] = [
                'id' => $product->id,
                'collection' => $item['collection'],
                'urls' => $item['urls'],
            ];
        }

        if ($mappedMediaPayload) {
            DispatchMediaImportBatchJob::dispatch(
                Product::class,
                $mappedMediaPayload,
                20
            )->onQueue('media');
        }
    }
}
