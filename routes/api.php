<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\SlugResolverController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/slug-resolver/{slug}', [SlugResolverController::class, 'resolver']);

    Route::middleware('throttle:login')->post('/login', LoginController::class);
    Route::middleware('throttle:register')->post('/register', RegisterController::class);
//    Route::middleware('throttle:forgot-password')->post('/forgot-password', ForgotPasswordController::class);
    Route::post('/forgot-password', ForgotPasswordController::class);
    Route::middleware('throttle:reset-password')->post('/reset-password', ResetPasswordController::class);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', LogoutController::class);

        Route::middleware('throttle:email-verify')
            ->get('/email/verify/{id}/{hash}', EmailVerificationController::class)
            ->name('verification.verify');
    });
});
