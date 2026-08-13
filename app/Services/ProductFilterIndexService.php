<?php
namespace App\Services;

use App\Models\Product;
use App\Models\ProductFilterIndex;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductFilterIndexService
{
    /**
     * @throws Throwable
     */
    public function rebuild(Product $product): void
    {
        DB::transaction(function () use ($product) {
            ProductFilterIndex::where('product_id', $product->id)->delete();

            $rows = [];
            $now = now();

            $categoryIds = $this->getCategoryIds($product);
            $collectionIds = $this->getCollectionIds($product);

            $categoryIds = $categoryIds->isEmpty() ? [null] : $categoryIds;
            $collectionIds = $collectionIds->isEmpty() ? [null] : $collectionIds;

            $terms = $product->attributeTerms;

            if ($terms->isEmpty()) {
                foreach ($categoryIds as $categoryId) {
                    foreach ($collectionIds as $collectionId) {
                        $rows[] = [
                            'product_id' => $product->id,
                            'category_id' => $categoryId,
                            'collection_id' => $collectionId,
                            'attribute_id' => null,
                            'attribute_term_id' => null,
                            'price' => $product->price_on_sale ?? $product->price,
                            'stock_status' => $product->stock_status,
                            'is_variant' => false,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            } else {
                foreach ($terms as $term) {
                    foreach ($categoryIds as $categoryId) {
                        foreach ($collectionIds as $collectionId) {
                            $rows[] = [
                                'product_id' => $product->id,
                                'category_id' => $categoryId,
                                'collection_id' => $collectionId,
                                'attribute_id' => $term->attribute_id,
                                'attribute_term_id' => $term->id,
                                'price' => $product->price_on_sale ?? $product->price,
                                'stock_status' => $product->stock_status,
                                'is_variant' => false,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }
            }

            foreach ($categoryIds as $categoryId) {
                foreach ($collectionIds as $collectionId) {
                    foreach ($product->variants as $variant) {
                        foreach ($variant->attributeTerms as $term) {
                            $rows[] = [
                                'product_id' => $product->id,
                                'category_id' => $categoryId,
                                'collection_id' => $collectionId,
                                'attribute_id' => $term->attribute_id,
                                'attribute_term_id' => $term->id,
                                'price' => $variant->price_on_sale ?? $variant->price,
                                'stock_status' => $variant->stock_status,
                                'is_variant' => true,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }
            }

            if (!empty($rows)) {
                foreach (array_chunk($rows, 1000) as $chunk) {
                    ProductFilterIndex::insert($chunk);
                }
            }
        });
    }

    private function getCategoryIds(Product $product): Collection
    {
        $categoryIds = collect();

        foreach ($product->categories as $category) {
            $current = $category;

            while ($current) {
                $categoryIds->push($current->id);
                $current = $current->parent;
            }
        }

        return $categoryIds->unique()->values();
    }

    private function getCollectionIds(Product $product): Collection
    {
        $collectionIds = collect();

        foreach ($product->collections as $collection) {
            $current = $collection;

            while ($current) {
                $collectionIds->push($current->id);
                $current = $current->parent;
            }
        }

        return $collectionIds->unique()->values();
    }
}
