<?php

use App\Http\Controllers\Api\V1\SlugResolverController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/slug-resolver/{slug}', [SlugResolverController::class, 'resolver']);
});
