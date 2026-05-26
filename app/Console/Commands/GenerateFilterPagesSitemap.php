<?php

namespace App\Console\Commands;

use App\Enums\EntityStatus;
use App\Models\FilterPage;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateFilterPagesSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:filter-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate filter pages sitemap';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sitemap = Sitemap::create();

        FilterPage::query()
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

        $sitemap->writeToFile(public_path('sitemaps/filter-pages.xml'));

        $this->info('Generated filter pages sitemap successfully');
    }
}
