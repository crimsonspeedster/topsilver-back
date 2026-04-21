<?php
namespace App\Services;

use App\Models\Product;
use App\Models\ProductFilterIndex;
use Illuminate\Support\Facades\DB;

class ProductFilterIndexService
{
    public function rebuild(Product $product): void
    {
        DB::transaction(function () use ($product) {
            // 1. очистить старые индексы
            ProductFilterIndex::where('product_id', $product->id)->delete();

            // 2. категории / коллекции
            $categoryIds = $product->categories->pluck('id');
            $collectionIds = $product->collections->pluck('id');

            $categoryIds = $categoryIds->isEmpty() ? [null] : $categoryIds;
            $collectionIds = $collectionIds->isEmpty() ? [null] : $collectionIds;

            // 3. product attributes (не вариации)
            foreach ($product->attributeTerms as $term) {
                foreach ($categoryIds as $categoryId) {
                    foreach ($collectionIds as $collectionId) {
                        ProductFilterIndex::create([
                            'product_id' => $product->id,
                            'category_id' => $categoryId, // или заполняешь
                            'collection_id' => $collectionId,
                            'attribute_id' => $term->attribute_id,
                            'attribute_term_id' => $term->id,
                            'price' => $product->price_on_sale ?? $product->price,
                            'stock_status' => $product->stock_status,
                            'is_variant' => false,
                        ]);
                    }
                }
            }

            // 4. variants
            foreach ($product->variants as $variant) {
                foreach ($variant->attributeTerms as $term) {
                    ProductFilterIndex::create([
                        'product_id' => $product->id,
                        'attribute_id' => $term->attribute_id,
                        'attribute_term_id' => $term->id,
                        'price' => $variant->price_on_sale ?? $variant->price,
                        'stock_status' => $variant->stock_status,
                        'is_variant' => true,
                    ]);
                }
            }
        });
    }
}
