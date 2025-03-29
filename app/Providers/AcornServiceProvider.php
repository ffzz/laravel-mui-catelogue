<?php

namespace App\Providers;

use App\Services\Acorn\AcornApiService;
use App\Services\Acorn\MockAcornApiService;
use App\Services\Content\ContentService;
use App\Services\HttpClient\HttpClientService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AcornServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register HTTP Client Service
        $this->app->singleton(HttpClientService::class, function ($app) {
            return new HttpClientService(config('acorn'));
        });

        // Register Acorn API Service
        $this->app->singleton(AcornApiService::class, function ($app) {
            // Only use mock service in testing environment, not in debug mode
            if (App::environment('testing')) {
                return new MockAcornApiService($app->make(HttpClientService::class));
            }

            // Use real service in all other environments (including debug mode)
            return new AcornApiService($app->make(HttpClientService::class));
        });

        // Register Content Service
        $this->app->singleton(ContentService::class, function ($app) {
            return new ContentService(
                $app->make(AcornApiService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
