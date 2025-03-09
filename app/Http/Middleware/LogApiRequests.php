<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;

class LogApiRequests
{
    public function handle($request, Closure $next)
    {
        $tracer = Globals::tracerProvider()->getTracer('laravel');
        $span = $tracer->spanBuilder('http.request')
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->setParent(Context::getCurrent())
            ->startSpan();

        try {
            // Set span attributes
            $span->setAttribute('http.method', $request->method());
            $span->setAttribute('http.url', $request->fullUrl());
            $span->setAttribute('http.user_agent', $request->header('User-Agent'));
            $span->setAttribute('http.client_ip', $request->ip());

            // Log request with trace context
            Log::info('API Request', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'request_data' => $request->all(),
                'trace_id' => $span->getContext()->getTraceId(),
            ]);

            // Process request
            $response = $next($request);

            // Set response attributes
            $span->setAttribute('http.status_code', $response->status());
            $span->setStatus($response->status() >= 500 ? StatusCode::STATUS_ERROR : StatusCode::STATUS_OK);

            // Log response
            Log::info('API Response', [
                'status_code' => $response->status(),
                'response_data' => $response->getContent(),
                'trace_id' => $span->getContext()->getTraceId(),
            ]);

            return $response;
        } catch (\Throwable $e) {
            // Record exception in span
            $span->recordException($e);
            $span->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());

            // Log error
            Log::error('API Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
                'trace_id' => $span->getContext()->getTraceId(),
            ]);

            throw $e;
        } finally {
            $span->end();
        }
    }
}
