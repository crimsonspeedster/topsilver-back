<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Instagram\Http\Controllers\InstagramController;

Route::get('/getData', [InstagramController::class, 'getData']);

Route::get('/auth', [InstagramController::class, 'auth']);

Route::get('/sync-posts', [InstagramController::class, 'sync']);

Route::get('/callback', [InstagramController::class, 'callback'])
    ->name('instagram.callback');

Route::delete('/delete', [InstagramController::class, 'remove']);
