<?php

use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Cart\CartBonusesController;
use App\Http\Controllers\Api\V1\Cart\CartCertificateController;
use App\Http\Controllers\Api\V1\Cart\CartController;
use App\Http\Controllers\Api\V1\Cart\CartCouponController;
use App\Http\Controllers\Api\V1\Cart\CartItemsController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\CheckoutSuccessController;
use App\Http\Controllers\Api\V1\LiqPayController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\MonopayController;
use App\Http\Controllers\Api\V1\NPController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PaymentMethodsController;
use App\Http\Controllers\Api\V1\ProductsController;
use App\Http\Controllers\Api\V1\EmailVerificationController;
use App\Http\Controllers\Api\V1\ResendEmailVerificationController;
use App\Http\Controllers\Api\V1\ReviewsController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\ShippingMethodsController;
use App\Http\Controllers\Api\V1\ShopsController;
use App\Http\Controllers\Api\V1\ShopsPickupController;
use App\Http\Controllers\Api\V1\SlugResolverController;
use App\Http\Controllers\Api\V1\SubscribersController;
use App\Http\Controllers\Api\V1\TaxonomyController;
use App\Http\Controllers\Api\V1\User\BonusController;
use App\Http\Controllers\Api\V1\User\OrdersController;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\User\UserUpdateController;
use App\Http\Controllers\Api\V1\WishlistController;
use App\Http\Controllers\Api\V1\BuyInOneClickController;
use App\Http\Middleware\ResolveCart;
use App\Http\Middleware\ResolveWishlist;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::middleware('throttle:api')->group(function () {
        Route::get('/slug-resolver/{slug}', [SlugResolverController::class, 'resolver']);
        Route::get('/slug-resolver/{slug}/seo', [SlugResolverController::class, 'seo']);

        Route::get('/home', [PageController::class, 'home']);
        Route::get('/home/seo', [PageController::class, 'home_seo']);

        Route::get('/shops', [ShopsController::class, 'index']);

        Route::get('/taxonomies/{type}/products/{id}', [TaxonomyController::class, 'show']);
        Route::get('/taxonomies/{type}/collections', [TaxonomyController::class, 'index']);

        Route::prefix('reference')->group(function () {
            Route::get('/cities', [CityController::class, 'cities']);
            Route::get('/categories', [CategoryController::class, 'categories']);
            Route::get('/payment-methods', PaymentMethodsController::class);
            Route::get('/shipping-methods', ShippingMethodsController::class);
            Route::get('/shops-pickup', ShopsPickupController::class);
        });

        Route::get('/menus', [MenuController::class, 'index']);
        Route::get('/menus/{location:name}', [MenuController::class, 'show']);

        Route::get('/settings', [SettingsController::class, 'index']);
        Route::get('/settings/{key}', [SettingsController::class, 'show']);

        Route::get('/products/batch', [ProductsController::class, 'batch']);

        Route::get('/products/{product}', [ProductsController::class, 'preview']);
        Route::get('/products/{product}/reviews', [ReviewsController::class, 'index']);
        Route::get('/reviews/{review}', [ReviewsController::class, 'replies']);
        Route::get('/checkout/success/{token}', [CheckoutSuccessController::class, 'show']);

        Route::prefix('nova-poshta')->group(function () {
            Route::get('/areas', [NPController::class, 'areas']);
            Route::get('/areas/{areaRef}/cities', [NPController::class, 'citiesByArea']);
            Route::get('/cities/{cityRef}/warehouses', [NPController::class, 'warehousesByCity']);

            Route::get('/locality', [NPController::class, 'localities']);
            Route::get('/locality/{localityRef}/streets', [NPController::class, 'streetsByCity']);
        });
    });

    Route::middleware('throttle:search')->get('/search', [SearchController::class, 'index']);

    Route::middleware('throttle:subscribe')->post('/subscribe', [SubscribersController::class, 'store']);
    Route::get('/unsubscribe/{token}', [SubscribersController::class, 'unsubscribe']);

    Route::middleware('throttle:notifications')->post('/products/{product}/notifications', [ProductsController::class, 'notifications']);

    Route::middleware('throttle:buy_in_one_click')->post('/buy-in-one-click', BuyInOneClickController::class);

    Route::middleware('throttle:login')->post('/login', LoginController::class);
    Route::middleware('throttle:register')->post('/register', RegisterController::class);
    Route::middleware('throttle:forgot-password')->post('/forgot-password', ForgotPasswordController::class);
    Route::middleware('throttle:reset-password')->post('/reset-password', ResetPasswordController::class);

    Route::post('/payments/liqpay/callback', [LiqpayController::class, 'callback'])
        ->name('payments.liqpay.callback');
    Route::post('/payments/monobank/callback', [MonopayController::class, 'callback'])
        ->name('payments.monobank.callback');

    Route::middleware('throttle:email-verify')
        ->post('/email/verify', EmailVerificationController::class);
    Route::middleware('throttle:6,1')
        ->post('/email/resend', ResendEmailVerificationController::class);

    Route::middleware([ResolveCart::class])->group(function () {
        Route::get('/cart', [CartController::class, 'show']);

        Route::middleware('throttle:cart')->group(function () {
            Route::post('/cart/items', [CartItemsController::class, 'store']);
            Route::patch('/cart/items/{id}', [CartItemsController::class, 'update']);
            Route::delete('/cart/items/{id}', [CartItemsController::class, 'destroy']);

            Route::post('/cart/coupon', [CartCouponController::class, 'store']);
            Route::delete('/cart/coupon', [CartCouponController::class, 'destroy']);
        });

        Route::middleware('throttle:checkout')->post('/checkout', CheckoutController::class);
    });

    Route::middleware([ResolveWishlist::class])->group(function () {
        Route::get('/wishlist', [WishlistController::class, 'show']);

        Route::middleware('throttle:wishlist')->group(function () {
            Route::post('/wishlist/items', [WishlistController::class, 'store']);
            Route::delete('/wishlist/items/{product_id}', [WishlistController::class, 'destroy']);
        });
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', LogoutController::class);
        Route::post('/products/{product}/reviews', [ReviewsController::class, 'store']);

        Route::prefix('me')->group(function () {
            Route::get('/', UserController::class);

            Route::patch('/profile', [UserUpdateController::class, 'profile']);
            Route::patch('/password', [UserUpdateController::class, 'password']);

            Route::get('/bonuses', BonusController::class);

            Route::get('/orders', [OrdersController::class, 'index']);
            Route::get('/orders/{order}', [OrdersController::class, 'show']);
        });
    });

    Route::middleware(['auth:sanctum', ResolveCart::class, 'throttle:cart'])->group(function () {
        Route::patch('/cart/bonuses', [CartBonusesController::class, 'apply']);

        Route::middleware('throttle:certificates')->post('/cart/certificates', [CartCertificateController::class, 'store']);

        Route::delete('/cart/certificates/{certificate}', [CartCertificateController::class, 'destroy']);
    });
});
