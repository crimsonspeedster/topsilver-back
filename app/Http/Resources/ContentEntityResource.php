<?php
namespace App\Http\Resources;

use App\Http\Resources\Product\ProductCardResource;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Page
 */

class ContentEntityResource extends JsonResource
{
    protected array $collectedRelations = [];

    protected array $relationCache = [];

    protected array $relationsMap = [
        'category' => [
            'model' => Category::class,
            'multiple' => false,
            'resource' => TaxonomyCollectionResource::class,
            'with' => [
                'sluggable',
            ],
        ],

        'product' => [
            'multiple' => false,
            'model' => Product::class,
            'resource' => ProductCardResource::class,
            'with' => [
                'sluggable',
                'variants',
                'labels',
                'categories.sluggable',
                'collections.sluggable',
            ],
        ],

        'products' => [
            'multiple' => true,
            'model' => Product::class,
            'resource' => ProductCardResource::class,
            'with' => [
                'sluggable',
                'variants',
                'labels',
                'categories.sluggable',
                'collections.sluggable',
            ],
        ],
    ];

    public function toArray($request): array
    {
        $this->collectedRelations = [];
        $this->relationCache = [];

        $this->collectRelations($this->content);
        $this->loadRelations();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'content' => $this->transformFlexibleContent($this->content),
            'seo_block' => new SeoBlockResource($this->whenLoaded('seoBlock')),
            'media' => new MediaResource($this->getFirstMedia('media')),
            'banner' => new MediaResource($this->getFirstMedia('banner')),
        ];
    }

    // -------------------------
    // COLLECT PHASE
    // -------------------------
    protected function collectRelations(array $content): void
    {
        collect($content)->each(function ($block) {

            if (!isset($block['attributes'])) {
                return;
            }

            $this->collectAttributes($block['attributes']);
        });
    }

    protected function collectAttributes(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            if (!isset($this->relationsMap[$key])) {
                continue;
            }

            $config = $this->relationsMap[$key];

            // SINGLE RELATION
            if (!$config['multiple']) {
                if (is_numeric($value)) {
                    $this->collectedRelations[$key][] = (int)$value;
                }

                continue;
            }

            // MULTIPLE RELATION
            $ids = is_string($value)
                ? json_decode($value, true)
                : $value;

            if (is_array($ids)) {
                $this->collectedRelations[$key] = array_merge(
                    $this->collectedRelations[$key] ?? [],
                    array_map('intval', $ids)
                );
            }

            // recursion
            if (is_array($value)) {
                $this->collectAttributes($value);
                continue;
            }

            if (is_string($value)) {
                $decoded = json_decode($value, true);

                if (is_array($decoded)) {
                    $this->collectAttributes($decoded);
                }
            }
        }
    }

    // -------------------------
    // LOAD PHASE
    // -------------------------
    protected function loadRelations(): void
    {
        foreach ($this->relationsMap as $key => $config) {

            $ids = $this->collectedRelations[$key] ?? [];
            $ids = array_filter($ids);

            if (!$ids) {
                continue;
            }

            $modelClass = $config['model'];

            $this->relationCache[$key] = $modelClass::query()
                ->with($config['with'] ?? [])
                ->whereIn('id', array_unique($ids))
                ->get()
                ->keyBy('id');
        }
    }

    // -------------------------
    // TRANSFORM PHASE
    // -------------------------
    protected function transformFlexibleContent(array $content): array
    {
        return collect($content)->map(function ($block) {

            if (!isset($block['attributes'])) {
                return $block;
            }

            $block['attributes'] = $this->transformAttributes(
                $block['attributes']
            );

            return $block;
        })->toArray();
    }

    protected function transformAttributes(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            // IMAGE
            if (
                $key === 'image'
                && is_string($value)
                && !str_starts_with($value, 'http')
            ) {
                $attributes[$key] = Storage::disk('public')->url($value);
                continue;
            }

            // RELATIONS
            if (isset($this->relationsMap[$key])) {

                $config = $this->relationsMap[$key];

                // SINGLE
                if (!$config['multiple'] && is_numeric($value)) {
                    $attributes[$key] = $this->resolveRelation($key, $value);
                    continue;
                }

                // MULTIPLE
                if ($config['multiple']) {

                    $ids = is_string($value)
                        ? json_decode($value, true)
                        : $value;

                    $ids = array_map('intval', array_filter($ids ?? []));

                    $attributes[$key] = collect($ids)
                        ->map(fn($id) => $this->resolveRelation($key, $id))
                        ->filter()
                        ->values();

                    continue;
                }
            }

            // recursion
            if (is_array($value)) {
                $attributes[$key] = $this->transformAttributes($value);
            }
        }

        return $attributes;
    }

    // -------------------------
    // RESOLVE
    // -------------------------
    protected function resolveRelation(string $key, mixed $value)
    {
        $value = (int)$value;

        $model = $this->relationCache[$key][$value] ?? null;

        if (!$model) {
            return null;
        }

        $resourceClass = $this->relationsMap[$key]['resource'];

        return new $resourceClass($model);
    }
}
