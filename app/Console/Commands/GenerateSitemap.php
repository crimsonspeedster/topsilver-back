<?php
namespace App\Console\Commands;

use App\Jobs\GenerateCategoriesSitemap;
use App\Jobs\GenerateCollectionsSitemap;
use App\Jobs\GenerateFilterPagesSitemap;
use App\Jobs\GenerateIndexSitemap;
use App\Jobs\GeneratePagesSitemap;
use App\Jobs\GenerateProductsSitemap;
use App\Jobs\GeneratePromotionsSitemap;
use App\Jobs\GenerateShopsSitemap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate sitemap';

    public function handle(): void
    {
        Bus::chain([
            new GenerateProductsSitemap(),
            new GeneratePagesSitemap(),
            new GenerateCategoriesSitemap(),
            new GenerateCollectionsSitemap(),
            new GeneratePromotionsSitemap(),
            new GenerateFilterPagesSitemap(),
            new GenerateShopsSitemap(),
            new GenerateIndexSitemap(),
        ])->onQueue('high')->dispatch();

        $this->info('Sitemap generated successfully.');
    }
}
