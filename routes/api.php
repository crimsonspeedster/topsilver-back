<?php

use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\ReviewsController;
use App\Http\Controllers\Api\V1\User\OrdersController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\SlugResolverController;
use App\Http\Controllers\Api\V1\User\BonusController;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\ProductsController;
use App\Http\Controllers\Api\V1\User\UserUpdateController;
use App\Http\Middleware\ResolveCart;
use App\Http\Controllers\Api\V1\Cart\CartController;
use App\Http\Controllers\Api\V1\Cart\CartItemsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/slug-resolver/{slug}', [SlugResolverController::class, 'resolver']);

    Route::get('/products/batch', [ProductsController::class, 'batch']);
    Route::get('/products/{product}', [ProductsController::class, 'preview']);
    Route::get('/products/{product}/reviews', [ReviewsController::class, 'index']);
    Route::get('/reviews/{review}', [ReviewsController::class, 'replies']);

    Route::middleware('throttle:login')->post('/login', LoginController::class);
    Route::middleware('throttle:register')->post('/register', RegisterController::class);
    Route::middleware('throttle:forgot-password')->post('/forgot-password', ForgotPasswordController::class);
    Route::middleware('throttle:reset-password')->post('/reset-password', ResetPasswordController::class);

    Route::middleware([ResolveCart::class])->group(function () {
        Route::get('/cart', [CartController::class, 'show']);

        Route::post('/cart/items', [CartItemsController::class, 'store']);
        Route::patch('/cart/items/{id}', [CartItemsController::class, 'update']);
        Route::delete('/cart/items/{id}', [CartItemsController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::middleware('throttle:email-verify')
            ->get('/email/verify/{id}/{hash}', EmailVerificationController::class)
            ->name('verification.verify');

        Route::post('/logout', LogoutController::class);
        Route::post('/products/{product}/reviews', [ReviewsController::class, 'store']);

        Route::patch('/me', UserUpdateController::class);

        Route::get('/me', UserController::class);
        Route::get('/me/bonuses', BonusController::class);
        Route::get('/me/orders', [OrdersController::class, 'index']);
        Route::get('/me/orders/{order}', [OrdersController::class, 'show']);
    });
});
