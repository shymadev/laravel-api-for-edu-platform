<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Auth\TokenGenerator;
use App\Services\Contracts\Auth\TokenGeneratorInterface;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TokenGeneratorInterface::class, TokenGenerator::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
