<?php
namespace App\Services;

use App\Models\Product;

class ProductService
{
//    public function getAttributes(Product $product): array
//    {
//        $terms = $product->attributeTerms()->with('attribute')->get();
//        $grouped = $terms->groupBy('attribute_id');
//        $result = [];
//
//        foreach ($grouped as $termsGroup) {
//            $attribute = $termsGroup->first()->attribute;
//
//            $result[] = [
//                'attribute' => [
//                    'id' => $attribute->id,
//                    'title' => $attribute->title,
//                    'slug' => $attribute->slug,
//                ],
//                'terms' => $termsGroup->map(function ($term) {
//                    return [
//                        'id' => $term->id,
//                        'title' => $term->title,
//                        'slug' => $term->slug,
//                        'meta_value' => $term->meta_value,
//                    ];
//                })->values()->all(),
//            ];
//        }
//
//        return $result;
//    }
}
