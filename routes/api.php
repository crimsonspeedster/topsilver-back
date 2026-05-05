<?php

use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\LiqPayController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\MonopayController;
use App\Http\Controllers\Api\V1\NPController;
use App\Http\Controllers\Api\V1\ReviewsController;
use App\Http\Controllers\Api\V1\User\OrdersController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\SettingsController;
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
use App\Http\Controllers\Api\V1\Cart\CartCouponController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/slug-resolver/{slug}', [SlugResolverController::class, 'resolver']);

    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/menus/{location:name}', [MenuController::class, 'show']);

    Route::get('/settings', [SettingsController::class, 'index']);
    Route::get('/settings/{key}', [SettingsController::class, 'show']);

    Route::get('/products/batch', [ProductsController::class, 'batch']);
    Route::get('/products/{product}', [ProductsController::class, 'preview']);
    Route::get('/products/{product}/reviews', [ReviewsController::class, 'index']);
    Route::get('/reviews/{review}', [ReviewsController::class, 'replies']);

    Route::middleware('throttle:login')->post('/login', LoginController::class);
    Route::middleware('throttle:register')->post('/register', RegisterController::class);
    Route::middleware('throttle:forgot-password')->post('/forgot-password', ForgotPasswordController::class);
    Route::middleware('throttle:reset-password')->post('/reset-password', ResetPasswordController::class);

    Route::post('/payments/liqpay/callback', [LiqpayController::class, 'callback'])
        ->name('payments.liqpay.callback');
    Route::post('/payments/monobank/callback', [MonopayController::class, 'callback'])
        ->name('payments.monobank.callback');

    Route::prefix('nova-poshta')->group(function () {
        Route::get('/areas', [NPController::class, 'areas']);
        Route::get('/areas/{areaRef}/cities', [NPController::class, 'citiesByArea']);
        Route::get('/cities/{cityRef}/warehouses', [NPController::class, 'warehousesByCity']);
    });

    Route::middleware([ResolveCart::class])->group(function () {
        Route::get('/cart', [CartController::class, 'show']);
        Route::post('/cart/items', [CartItemsController::class, 'store']);
        Route::patch('/cart/items/{id}', [CartItemsController::class, 'update']);
        Route::delete('/cart/items/{id}', [CartItemsController::class, 'destroy']);

        Route::post('/cart/coupon', [CartCouponController::class, 'store']);
        Route::delete('/cart/coupon', [CartCouponController::class, 'destroy']);

        Route::post('/checkout', CheckoutController::class);
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
