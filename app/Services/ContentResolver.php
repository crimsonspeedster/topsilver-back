<?php
namespace App\Services;

use App\Http\Resources\InstagramPostResource;
use App\Http\Resources\MenuItemResource;
use App\Http\Resources\Product\ProductCardResource;
use App\Http\Resources\TaxonomyCollectionResource;
use App\Models\Category;
use App\Models\InstagramPost;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Support\Facades\Storage;

class ContentResolver
{
    public function resolve(array $blocks): array
    {
        return collect($blocks)->map(function ($block) {
            return match ($block['layout']) {
                'InstagramGrid' => $this->resolveInstagramGrid($block),
                'CategoriesGrid' => $this->resolveCategoriesGrid($block),
                'ProductsGrid' => $this->resolveProductsGrid($block),
                'ProductsGridWithTabs' => $this->resolveProductsGridWithTabs($block),
                'LatestPromotions' => $this->resolveLatestPromotions($block),
                'MegaMenu' => $this->resolveMegaMenu($block),
                default => $block,
            };
        })->toArray();
    }

    private function resolveMegaMenu(array $block): array
    {
        $left_part = $block['attributes']['left_part'] ?? [];
        $right_part = $block['attributes']['right_part'] ?? [];

        $leftPartCount = count($left_part);

        $parts = array_merge($left_part, $right_part);

        $parts = array_map(function ($part) {
            if ($part['layout'] === 'MenuItem') {
                return $this->resolveMenuItem($part);
            }
            elseif ($part['layout'] === 'MenuImage') {
                return $this->resolveMenuImage($part);
            }

            return $part;
        }, $parts);

        $block['attributes']['left_part'] = array_slice($parts, 0, $leftPartCount);
        $block['attributes']['right_part'] = array_slice($parts, $leftPartCount);

        return $block;
    }

    private function resolveMenuImage(array $block): array
    {
        $image_name = $block['attributes']['image'];
        $image_url = Storage::disk('public')->url($image_name);

        $block['attributes']['image'] = $image_url;

        return $block;
    }

    private function resolveMenuItem(array $block): array
    {
        $ids = json_decode($block['attributes']['menu_items'] ?? '[]', true);
        $posts = MenuItem::whereIn('id', $ids)
            ->get();

        $block['attributes']['menu_items'] = MenuItemResource::collection($posts);

        return $block;
    }

    private function resolveLatestPromotions(array $block): array
    {
        $ids = json_decode($block['attributes']['promotions'] ?? '[]', true);
        $posts = Promotion::whereIn('id', $ids)
            ->get()
            ->load([
                'sluggable',
            ]);

        $block['attributes']['promotions'] = TaxonomyCollectionResource::collection($posts);

        return $block;
    }

    private function resolveProductsGrid(array $block): array
    {
        $ids = json_decode($block['attributes']['products'] ?? '[]', true);
        $posts = Product::whereIn('id', $ids)->get()
            ->load([
                'sluggable',
                'labels',
            ]);

        $block['attributes']['products'] = ProductCardResource::collection($posts);

        return $block;
    }

    private function resolveInstagramGrid(array $block): array
    {
        $posts = InstagramPost::whereHas('media', function ($query) {
            $query->where('collection_name', 'media');
        })
            ->with(['media' => function ($query) {
                $query->whereIn('collection_name', ['media', 'video']);
            }])
            ->latest()
            ->take(12)
            ->get()
            ->reverse()
            ->values();

        $block['attributes']['posts'] = InstagramPostResource::collection($posts);

        return $block;
    }

    private function resolveCategoriesGrid(array $block): array
    {
        $block['attributes']['categories'] = collect($block['attributes']['categories'])
            ->map(function ($item) {
                return $this->resolveCategoriesGridItem($item);
            })->toArray();

        return $block;
    }

    private function resolveCategoriesGridItem(array $item): array
    {
        $categoryId = $item['attributes']['category'] ?? null;

        if ($categoryId) {
            $category = Category::find($categoryId)->load(['sluggable']);

            $item['attributes']['category'] =
                new TaxonomyCollectionResource($category);
        }

        return $item;
    }

    private function resolveProductsGridWithTabs(array $block): array
    {
        $block['attributes']['blocks'] = collect($block['attributes']['blocks'])
            ->map(function ($item) {
                return $this->resolveProductsGridWithTabItem($item);
            })->toArray();

        return $block;
    }

    private function resolveProductsGridWithTabItem(array $item): array
    {
        $ids = json_decode($item['attributes']['products'] ?? '[]', true);
        $posts = Product::whereIn('id', $ids)->get()
            ->load([
                'sluggable',
                'labels',
            ]);

        $item['attributes']['products'] = ProductCardResource::collection($posts);

        return $item;
    }
}
