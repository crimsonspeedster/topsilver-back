<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sync:instagram-posts')
    ->dailyAt('01:00')
    ->timezone('Europe/Kyiv');

Schedule::command('sitemap:generate')
    ->dailyAt('02:00')
    ->timezone('Europe/Kyiv');

Schedule::command('sync:nova-poshta')
    ->sundays()
    ->at('03:00')
    ->timezone('Europe/Kyiv');
