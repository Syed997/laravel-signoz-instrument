<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SemConv\ResourceAttributes;

class OpenTelemetryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Tracer::class, function () {
            $transportFactory = new OtlpHttpTransportFactory();
            $transport = $transportFactory->create(
                env('OTLP_ENDPOINT', 'http://localhost:4318/v1/traces'),
                'application/x-protobuf'
            );

            $exporter = new SpanExporter($transport);
            $spanProcessor = new SimpleSpanProcessor($exporter);

            $resource = ResourceInfo::create(
                Attributes::create([
                    ResourceAttributes::SERVICE_NAME => config('app.name', 'Laravel App'),
                ])
            );

            $tracerProvider = new TracerProvider([$spanProcessor], null, $resource);

            return $tracerProvider->getTracer('laravel-tracer');
        });

    }

    public function boot()
    {
        //
    }
}
