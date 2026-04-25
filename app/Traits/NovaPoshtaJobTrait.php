<?php
namespace App\Traits;

use App\Services\NovaPoshtaService;

trait NovaPoshtaJobTrait
{
    protected function service(): NovaPoshtaService
    {
        return app(NovaPoshtaService::class);
    }
}
