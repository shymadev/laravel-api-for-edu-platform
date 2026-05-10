<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Registers Swagger UI gate: who may view API docs in non-local environments.
 */
class SwaggerUiServiceProvider extends ServiceProvider
{
    /**
     * Define the `viewSwaggerUI` authorization gate.
     *
     * @return void
     */
    public function boot(): void
    {
        Gate::define('viewSwaggerUI', function ($user = null) {
            return app()->environment('local') || in_array(optional($user)->email, [
                //
            ]);
        });
    }
}
