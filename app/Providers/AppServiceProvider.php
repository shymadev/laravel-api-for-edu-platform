<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\SendPasswordResetSuccessEmail;
use App\Models\User\PersonalAccessToken;
use App\Models\User\User;
use App\Services\ChangePasswordProcessor;
use App\Services\Strategy\ChangePassword\GoogleChangePasswordStrategy;
use App\Services\Strategy\ChangePassword\StandardChangePasswordStrategy;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Sanctum\Sanctum;
use Stripe\Stripe;

/**
 * Application-wide service registration and bootstrapping (bindings, Sanctum, Cashier, events).
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->register(CacheServiceProvider::class);

        $this->app->bind(ChangePasswordProcessor::class, function ($app) {
            return new ChangePasswordProcessor(
                strategies: [
                    $app->make(GoogleChangePasswordStrategy::class),
                    $app->make(StandardChangePasswordStrategy::class),
                ],
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Cashier::useCustomerModel(User::class);
        Stripe::setApiKey(config('cashier.secret'));

        Event::listen(PasswordReset::class, SendPasswordResetSuccessEmail::class);
    }
}
