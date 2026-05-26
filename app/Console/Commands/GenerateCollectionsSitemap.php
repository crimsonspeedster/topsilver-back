<?php

namespace App\Console\Commands;

use App\Enums\EntityStatus;
use App\Models\Collection;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateCollectionsSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:collections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate collections sitemap';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sitemap = Sitemap::create();

        Collection::query()
            ->with('sluggable')
            ->where('status', EntityStatus::Published)
            ->select(['id', 'updated_at'])
            ->chunk(500, function ($entities) use ($sitemap) {
                foreach ($entities as $entity) {
                    $slug = $entity->sluggable?->slug;

                    if (!$slug) {
                        continue;
                    }

                    $sitemap->add(
                        Url::create(frontend_url("/{$slug}"))
                            ->setLastModificationDate($entity->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                }
            });

        $sitemap->writeToFile(public_path('sitemaps/collections.xml'));

        $this->info('Generated collections sitemap successfully');
    }
}
