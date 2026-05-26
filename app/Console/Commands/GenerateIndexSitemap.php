<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Tags\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Illuminate\Support\Facades\File;

class GenerateIndexSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate index sitemap';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SitemapIndex::create()
            ->add(
                Sitemap::create(frontend_url('/sitemaps/pages.xml'))
                    ->setLastModificationDate(
                        now()->setTimestamp(
                            File::lastModified(public_path('sitemaps/pages.xml'))
                        )
                    )
            )
            ->add(
                Sitemap::create(frontend_url('/sitemaps/products.xml'))
                    ->setLastModificationDate(
                        now()->setTimestamp(
                            File::lastModified(public_path('sitemaps/products.xml'))
                        )
                    )
            )
            ->add(
                Sitemap::create(frontend_url('/sitemaps/shops.xml'))
                    ->setLastModificationDate(
                        now()->setTimestamp(
                            File::lastModified(public_path('sitemaps/shops.xml'))
                        )
                    )
            )
            ->add(
                Sitemap::create(frontend_url('/sitemaps/categories.xml'))
                    ->setLastModificationDate(
                        now()->setTimestamp(
                            File::lastModified(public_path('sitemaps/categories.xml'))
                        )
                    )
            )
            ->add(
                Sitemap::create(frontend_url('/sitemaps/filter-pages.xml'))
                    ->setLastModificationDate(
                        now()->setTimestamp(
                            File::lastModified(public_path('sitemaps/filter-pages.xml'))
                        )
                    )
            )
            ->writeToFile(public_path('sitemap_index.xml'));

        $this->info('Generated index sitemap successfully');
    }
}
