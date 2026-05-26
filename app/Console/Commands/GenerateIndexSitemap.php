<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;

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
        Sitemap::create()
            ->add(frontend_url('/sitemaps/pages.xml'))
            ->add(frontend_url('/sitemaps/collections.xml'))
            ->add(frontend_url('/sitemaps/categories.xml'))
            ->add(frontend_url('/sitemaps/filter-pages.xml'))
            ->add(frontend_url('/sitemaps/products.xml'))
            ->add(frontend_url('/sitemaps/shops.xml'))
            ->writeToFile(public_path('sitemap_index.xml'));

        $this->info('Generated index sitemap successfully');
    }
}
