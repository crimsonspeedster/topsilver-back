<?php

namespace App\Providers;

use App\Events\UserLoggedIn;
use App\Events\UserRegistered;
use App\Listeners\AttachOneClickRequestsToUser;
use App\Listeners\AttachOrdersToUser;
use App\Listeners\MergeCartListener;
use App\Listeners\MergeWishlistListener;
use App\Models\AttributeTerm;
use App\Models\Category;
use App\Models\Collection;
use App\Models\OneClickRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Observers\AttributeTermObserver;
use App\Observers\CategoryObserver;
use App\Observers\CollectionObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductReviewObserver;
use App\Policies\ProductReviewPolicy;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->sanctumSettings();
        $this->includePolicies();
        $this->configureRoutes();
        $this->configureEmails();
        $this->configureRateLimiting();
        $this->customListeners();
        $this->observeHandle();
    }

    protected function sanctumSettings(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    protected function includePolicies (): void
    {
        Gate::policy(ProductReview::class, ProductReviewPolicy::class);
    }

    protected function observeHandle (): void
    {
        Product::observe(ProductObserver::class);
        AttributeTerm::observe(AttributeTermObserver::class);
        Collection::observe(CollectionObserver::class);
        Category::observe(CategoryObserver::class);
        ProductReview::observe(ProductReviewObserver::class);
        Order::observe(OrderObserver::class);
        OneClickRequest::observe(OrderObserver::class);
    }

    protected function configureRoutes (): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }

    protected function configureEmails () : void
    {
        VerifyEmail::createUrlUsing(function ($notifiable) {
            return config('app.frontend_url') . '/verify-email?' . http_build_query([
                'email' => $notifiable->getEmailForVerification(),
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]);
        });

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('app.frontend_url') . "/reset-password?token={$token}&email={$user->email}";
        });
    }

    protected function configureRateLimiting (): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower($request->input('email') ?? '') ?: $request->ip();

            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perMinute(5)->by($email),
                Limit::perHour(50)->by($request->ip()),
            ];
        });

        RateLimiter::for('register', function (Request $request) {
            return [
                Limit::perMinute(3)->by($request->ip()),
                Limit::perHour(20)->by($request->ip()),
            ];
        });

        RateLimiter::for('forgot-password', function (Request $request) {
            $email = strtolower($request->input('email') ?? '') ?: $request->ip();

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(2)->by($email),
                Limit::perHour(5)->by($email),
            ];
        });

        RateLimiter::for('reset-password', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(20)->by($request->ip()),
            ];
        });

        RateLimiter::for('email-verify', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return [
                Limit::perMinute(2)->by($key),
                Limit::perHour(20)->by($key),
            ];
        });

        RateLimiter::for('api', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return Limit::perMinute(120)->by($key);
        });

        RateLimiter::for('cart', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return Limit::perMinute(30)->by($key);
        });

        RateLimiter::for('certificates', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return [
                Limit::perMinutes(15, 5)->by($key),
                Limit::perHour(20)->by($key),
            ];
        });

        RateLimiter::for('checkout', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('subscribe', function (Request $request) {
            $email = strtolower($request->input('email') ?? '') ?: $request->ip();

            return [
                Limit::perHour(10)->by($request->ip()),
                Limit::perHour(3)->by($email),
            ];
        });

        RateLimiter::for('notifications', function (Request $request) {
            $key = $request->user()?->id
                ?: strtolower($request->input('email') ?? '')
                    ?: $request->ip();

            return Limit::perHour(20)->by($key);
        });

        RateLimiter::for('wishlist', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return Limit::perMinute(30)->by($key);
        });

        RateLimiter::for('buy_in_one_click', function (Request $request) {
            $key = $request->input('phone') ?? $request->ip();

            return [
                Limit::perMinute(3)->by($request->ip()),
                Limit::perHour(5)->by($key),
            ];
        });
    }

    protected function customListeners (): void
    {
        Event::listen(UserLoggedIn::class, AttachOrdersToUser::class);

        Event::listen(UserLoggedIn::class, AttachOneClickRequestsToUser::class);

        Event::listen(UserLoggedIn::class, MergeCartListener::class);

        Event::listen(UserLoggedIn::class, MergeWishlistListener::class);
    }
}
