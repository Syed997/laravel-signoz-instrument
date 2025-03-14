<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LoggerProviderFactory;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use Illuminate\Support\Facades\Log;

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
        if (env('OTEL_ENABLED', true)) {
            try {
                $transport = (new OtlpHttpTransportFactory())->create(
                    env('OTEL_EXPORTER_OTLP_TRACES_ENDPOINT', 'http://signoz-otel-collector:4318/v1/traces'),
                    'application/json'
                );
                $exporter = new SpanExporter($transport);

                // Define resource attributes using AttributesInterface
                $attributes = Attributes::create([
                    ResourceAttributes::SERVICE_NAME => env('OTEL_SERVICE_NAME', 'Laravel-Instrumentation'),
                ]);
                $resource = ResourceInfo::create($attributes);

                // Set up the tracer provider
                $tracerProvider = new TracerProvider(
                    new SimpleSpanProcessor($exporter),
                    null, // Sampler (default is AlwaysOn)
                    $resource
                );

                // Register the tracer provider globally
                Configurator::create()
                    ->withTracerProvider($tracerProvider)
                    ->activate();

                // Optional: Shut down cleanly on app termination
                register_shutdown_function(function () use ($tracerProvider) {
                    $tracerProvider->shutdown();
                });
            } catch (\Exception $e) {
                Log::warning("Failed to initialize OpenTelemetry: " . $e->getMessage());
            }
        }
    }
}
