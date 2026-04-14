<?php

use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\User\OrdersController;
use App\Http\Controllers\Api\V1\User\OrderSingleController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\SlugResolverController;
use App\Http\Controllers\Api\V1\User\BonusController;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\User\UserUpdateController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/slug-resolver/{slug}', [SlugResolverController::class, 'resolver']);

    Route::middleware('throttle:login')->post('/login', LoginController::class);
    Route::middleware('throttle:register')->post('/register', RegisterController::class);
    // надо добавить qa
    Route::middleware('throttle:forgot-password')->post('/forgot-password', ForgotPasswordController::class);
    // надо добавить qa
    Route::middleware('throttle:reset-password')->post('/reset-password', ResetPasswordController::class);

    Route::middleware(['auth:sanctum'])->group(function () {
        // надо добавить qa
        Route::middleware('throttle:email-verify')
            ->get('/email/verify/{id}/{hash}', EmailVerificationController::class)
            ->name('verification.verify');

        Route::post('/logout', LogoutController::class);

        Route::get('/me', UserController::class);
        Route::patch('/me', UserUpdateController::class);
        // надо добавить qa
        Route::get('/me/bonuses', BonusController::class);
        // надо добавить qa
        Route::get('/me/orders', OrdersController::class);
        // надо добавить qa
        Route::get('/me/orders/{id}', OrderSingleController::class);
    });
});
