<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SyncAreasJob;
use App\Jobs\SyncCitiesJob;
use App\Jobs\SyncWarehousesStartJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    Bus::chain([
        new SyncAreasJob(),
        new SyncCitiesJob(),
        new SyncWarehousesStartJob(),
    ])->dispatch();
})
    ->dailyAt('09:57')
    ->timezone('Europe/Warsaw');;

//Schedule::call(function () {
//    dispatch(new SyncAreasJob());
//})
////    ->weekly();
//
//Schedule::call(function () {
//    dispatch(new SyncCitiesJob());
//})
////    ->cron('0 0 */5 * *');
//
//Schedule::call(function () {
//    dispatch(new SyncWarehousesJob());
//})
////    ->cron('0 0 */3 * *');
