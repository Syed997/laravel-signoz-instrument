<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LoggerProviderFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoggerProviderInterface::class, function () {
            return (new LoggerProviderFactory())->create();
        });

        $this->app->bind(LoggerInterface::class, function ($app) {
            return $app->make(LoggerProviderInterface::class)
                ->getLogger('laravel', '1.0.0');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
