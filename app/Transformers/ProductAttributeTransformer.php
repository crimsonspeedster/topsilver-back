<?php

namespace App\Transformers;

use App\Http\Resources\AttributeResource;
use App\Http\Resources\AttributeTermResource;

class ProductAttributeTransformer
{
    public static function make($terms)
    {
        return $terms->groupBy('attribute_id')->map(function ($terms) {
            return [
                'attribute' => new AttributeResource($terms->first()->attribute),
                'terms' => AttributeTermResource::collection($terms),
            ];
        })->values();
    }
}
