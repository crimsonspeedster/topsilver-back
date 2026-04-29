<?php
namespace App\Services;

use App\DTO\Money;

class CurrencyService
{
    public function format(float $price): Money
    {
        $currency = config('app.currency');

        return new Money($price, $currency);
    }
}
