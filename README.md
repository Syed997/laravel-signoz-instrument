# FLY EYES - PHP Application

This document provides details about the "FLY EYES" PHP application, including its location, startup instructions, and API call process.

## Application Details

- **Name**: FLY EYES
- **Type**: PHP Application
- **Purpose**: Sends data to Signoz


## Functionality

The PHP application sends data(http request, database call, logs, external api call matrics) to Signoz, an observability platform, for monitoring or analytics purposes.

## How the Application Sends Data to Signoz

The PHP application integrates with Signoz using OpenTelemetry auto-instrumentation to collect and export telemetry data (traces, metrics, and logs) without manual span creation. This is achieved through a PHP extension, Composer packages, and environment variables.

## Auto-Instrumentation Configuration (Zero Code Instrument)

PHP Extension Installation:

The OpenTelemetry PHP extension is installed in the Docker environment to enable auto-instrumentation at the runtime level:
```
RUN pecl install opentelemetry-1.0.0beta3 && docker-php-ext-enable opentelemetry
```

Composer Packages:

The following packages are included in the composer.json to support OpenTelemetry auto-instrumentation and OTLP export:
```
    "open-telemetry/exporter-otlp": "^1.2",
    "open-telemetry/opentelemetry-auto-laravel": "^1.0",
    "open-telemetry/opentelemetry-auto-slim": "^1.0",
    "open-telemetry/opentelemetry-logger-monolog": "^1.0",
    "open-telemetry/sdk": "^1.2",
    "php-http/guzzle7-adapter": "^1.1",
```
"open-telemetry/opentelemetry-auto-laravel": "^1.0" - Provides Laravel-specific auto-instrumentation.
"open-telemetry/sdk": Core OpenTelemetry SDK for PHP.
"open-telemetry/exporter-otlp": Handles exporting telemetry data via OTLP.
"php-http/guzzle7-adapter": HTTP client adapter for sending data over OTLP.


Environment Variables:

Auto-instrumentation is configured via environment variables in the .env file:
```
OTEL_SERVICE_NAME=Laravel-Instrumentation
OTEL_EXPORTER_OTLP_ENDPOINT=http://10.104.10.140:44318/v1/traces
OTEL_EXPORTER_OTLP_TRACES_ENDPOINT=http://10.104.10.140:44318/v1/traces
OTEL_EXPORTER_OTLP_LOGS_ENDPOINT=http://10.104.10.140:44318/v1/logs
OTEL_PHP_AUTOLOAD_ENABLED=true
OTEL_TRACES_EXPORTER=otlp
OTEL_EXPORTER_OTLP_PROTOCOL=http/protobuf
OTEL_LOG_LEVEL=info
OTEL_PROPAGATORS=baggage,tracecontext
OTEL_ENABLED=true
```
OTEL_SERVICE_NAME: Defines the service name displayed in Signoz (e.g., "Laravel-Instrumentation").
OTEL_EXPORTER_OTLP_ENDPOINT: Specifies the Signoz endpoint for traces.
OTEL_TRACES_EXPORTER: Sets the exporter to OTLP.
OTEL_EXPORTER_OTLP_PROTOCOL: Configures the protocol to http/protobuf for Protobuf over HTTP.
OTEL_PHP_AUTOLOAD_ENABLED: Enables auto-instrumentation at the PHP level.
OTEL_PROPAGATORS: Configures context propagation (e.g., baggage and tracecontext).
OTEL_ENABLED: Globally enables OpenTelemetry.


## Data Collection (Controller)

The open-telemetry/opentelemetry-auto-laravel package automatically instruments Laravel components such as HTTP requests, middleware, database queries (e.g., Eloquent), and other framework-level operations.

