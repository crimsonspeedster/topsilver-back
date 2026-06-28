<?php
namespace App\Support;

class VariantKeyGenerator
{
    public static function make(iterable $terms): string
    {
        return collect($terms)
            ->sortBy('attribute_id')
            ->map(fn ($term) => "{$term->attribute_id}:{$term->id}")
            ->implode('|');
    }
}
