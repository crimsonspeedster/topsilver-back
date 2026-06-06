<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\File;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Sitemap;

class GenerateIndexSitemap implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
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
                Sitemap::create(frontend_url('/sitemaps/collections.xml'))
                    ->setLastModificationDate(
                        now()->setTimestamp(
                            File::lastModified(public_path('sitemaps/collections.xml'))
                        )
                    )
            )
            ->add(
                Sitemap::create(frontend_url('/sitemaps/promotions.xml'))
                    ->setLastModificationDate(
                        now()->setTimestamp(
                            File::lastModified(public_path('sitemaps/promotions.xml'))
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
    }
}
