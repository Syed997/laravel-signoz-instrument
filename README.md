# FLY EYES - PHP Application

This document provides details about the "FLY EYES" PHP application, including its location, startup instructions, and API call process.

## Application Details

- **Name**: FLY EYES
- **Type**: PHP Application
- **Purpose**: Sends data to Signoz

## Application Location

To navigate to the application directory:

```bash
cd /data/apps/php-apps/test-project

```

## Starting the PHP Application

To start the PHP application using Docker Compose:

```bash
cd /data/apps/php-apps/test-project
docker-compose up
```
## API Call

To execute the API call script:

```bash
cd /data/apps/php-apps
./apiCall.sh
```
## Functionality

The PHP application sends data to Signoz, an observability platform, for monitoring or analytics purposes.

## How the Application Sends Data to Signoz

The application integrates with Signoz using OpenTelemetry, a framework for collecting and exporting telemetry data (traces, metrics, and logs). Below is an overview of how it sends data:

## OpenTelemetry Setup (Service Provider)

The telemetry functionality is configured in the OpenTelemetryServiceProvider class, which registers a Tracer singleton in the Laravel application:

Tracer Initialization:
A TracerProvider is created with a SimpleSpanProcessor and a SpanExporter.
The SpanExporter uses the OTLP (OpenTelemetry Protocol) over HTTP to send data to Signoz.
The endpoint for Signoz is configurable via the OTLP_ENDPOINT environment variable (defaults to http://localhost:4318/v1/traces).
The data is sent in Protobuf format (application/x-protobuf).

Resource Configuration:
The application attaches metadata, such as the service name (e.g., "Laravel App"), to the telemetry data using ResourceInfo.

```
$transport = $transportFactory->create(env('OTLP_ENDPOINT', 'http://localhost:4318/v1/traces'), 'application/x-protobuf');
$exporter = new SpanExporter($transport);
$spanProcessor = new SimpleSpanProcessor($exporter);
$tracerProvider = new TracerProvider([$spanProcessor], null, $resource);
return $tracerProvider->getTracer('laravel-tracer');

```

## Data Collection (Controller)

The PersonController uses the injected Tracer to create and manage spans for API operations (e.g., index, store, show, update, destroy). These spans track the execution of HTTP requests and database interactions, which are then sent to Signoz.

Span Creation:
For each API method, a root span is created (e.g., GET /api/people, POST /api/people) with attributes like HTTP method, route, and status code.
Nested spans (e.g., DB Fetch, DB Insert) track database operations, including the database system (postgres) and SQL statements.

Attributes and Status:
Spans are enriched with attributes (e.g., http.method, db.statement) and statuses (STATUS_OK or STATUS_ERROR) based on the operation’s success or failure.

Error Handling:
If an exception occurs, the span records the error with an appropriate HTTP status code (e.g., 404 for "Not Found", 500 for "Internal Server Error") and ends the span.

Example from the index method:
```
$span = $this->tracer->spanBuilder('GET /api/people')->startSpan();
$span->setAttribute('http.method', 'GET');
$span->setAttribute('http.route', '/api/people');

$dbSpan = $this->tracer->spanBuilder('DB Fetch')->startSpan();
$dbSpan->setAttribute('db.system', 'postgres');
$dbSpan->setAttribute('db.statement', 'SELECT * FROM people');

$people = Person::all();
$dbSpan->setStatus(StatusCode::STATUS_OK);
$dbSpan->end();

$span->setStatus(StatusCode::STATUS_OK);
$span->end();
```

## Data Transmission

Span Export: Once a span ends (via $span->end()), the SimpleSpanProcessor immediately forwards it to the SpanExporter.

OTLP Protocol: The exporter sends the span data to the configured Signoz endpoint over HTTP using OTLP.

Signoz Visualization: Signoz receives the traces, processes them, and makes them available for monitoring, allowing developers to analyze request latencies, database performance, and error rates.
