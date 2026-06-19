<?php

namespace App\Jobs;

use App\Models\Seo;
use App\Models\Slug;
use App\Services\SeoGenerateService;
use App\Services\SlugGenerateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateEntityMetaJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $modelClass,
        public array $ids,
    ) {}

    public function handle(
        SlugGenerateService $slugService,
        SeoGenerateService $seoService,
    ): void {
        if (empty($this->ids)) {
            return;
        }

        $models = $this->modelClass::query()
            ->whereIn('id', $this->ids)
            ->get();

        if ($models->isEmpty()) {
            return;
        }

        $now = now();

        $entityIds = $models
            ->pluck('id')
            ->all();

        $existingSlugs = Slug::query()
            ->where('entity_type', $this->modelClass)
            ->whereIn('entity_id', $entityIds)
            ->pluck('entity_id')
            ->flip()
            ->toArray();

        $existingSeo = Seo::query()
            ->where('entity_type', $this->modelClass)
            ->whereIn('entity_id', $entityIds)
            ->pluck('entity_id')
            ->flip()
            ->toArray();

        $allSlugs = Slug::query()
            ->pluck('slug')
            ->toArray();

        $slugRows = [];
        $seoRows = [];

        foreach ($models as $model) {
            if (
                !method_exists($model, 'getSeoTitle') ||
                !method_exists($model, 'getSeoDescription')
            ) {
                continue;
            }

            $title = trim((string) $model->getSeoTitle());

            if ($title === '') {
                continue;
            }

            if (!isset($existingSlugs[$model->id])) {
                $slug = $slugService->generate(
                    $title,
                    $allSlugs
                );

                $allSlugs[] = $slug;

                $slugRows[] = [
                    'slug' => $slug,
                    'entity_type' => $this->modelClass,
                    'entity_id' => $model->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!isset($existingSeo[$model->id])) {
                $seo = $seoService->generateSeo(
                    $title,
                    $model->getSeoDescription(),
                    null
                );

                $seoRows[] = [
                    'entity_type' => $this->modelClass,
                    'entity_id' => $model->id,
                    'title' => $seo['title'],
                    'description' => $seo['description'],
                    'keywords' => $seo['keywords'],
                    'robots' => $seo['robots'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($slugRows) {
            Slug::upsert(
                $slugRows,
                ['entity_type', 'entity_id'],
                [
                    'slug',
                    'updated_at',
                ]
            );
        }

        if ($seoRows) {
            Seo::upsert(
                $seoRows,
                ['entity_type', 'entity_id'],
                [
                    'title',
                    'description',
                    'keywords',
                    'robots',
                    'updated_at',
                ]
            );
        }
    }
}
