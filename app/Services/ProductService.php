<?php
namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function getBreadcrumbs(Product $product): array
    {
        $breadcrumbs = [
            [
                'title' => 'Головна',
                'slug'   => '/',
            ],
        ];

        $category = $product->categories()->first();

        if ($category) {
            foreach ($category->ancestors() as $ancestor) {
                $breadcrumbs[] = [
                    'title' => $ancestor->title,
                    'slug'   => $ancestor->sluggable?->slug,
                ];
            }

            $breadcrumbs[] = [
                'title' => $category->title,
                'slug'   => $category->sluggable?->slug,
            ];
        }

        $breadcrumbs[] = [
            'title' => $product->title,
            'slug'   => null,
        ];

        return $breadcrumbs;
    }

    public function getPrev(Product $product): ?Product
    {
        return Product::query()
            ->published()
            ->where('id', '<', $product->id)
            ->orderByDesc('id')
            ->with([
                'sluggable',
                'labels'
            ])
            ->first();
    }

    public function getNext(Product $product): ?Product
    {
        return Product::query()
            ->published()
            ->where('id', '>', $product->id)
            ->orderBy('id')
            ->with([
                'sluggable',
                'labels'
            ])
            ->first();
    }
}
