<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use OpenTelemetry\API\Trace\StatusCode;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use OpenTelemetry\SDK\Trace\Tracer;

class ExternalApiController extends Controller
{
    protected $tracer;

    public function __construct(Tracer $tracer)
    {
        $this->tracer = $tracer;
    }

    public function fetchPeople()
    {
        $span = $this->tracer->spanBuilder('GET /api/external/people')->startSpan();
        try {
            $span->setAttribute('http.method', 'GET');
            $span->setAttribute('http.route', '/api/external/people');

            $apiSpan = $this->tracer->spanBuilder('External API Fetch')->startSpan();
            $apiSpan->setAttribute('http.url', 'https://jsonplaceholder.typicode.com/users');
            $apiSpan->setAttribute('http.method', 'GET');

            try {
                $response = Http::timeout(10)->get('https://jsonplaceholder.typicode.com/users');

                if (!$response->successful()) {
                    $apiSpan->setAttribute('http.status_code', $response->status());
                    $apiSpan->setStatus(StatusCode::STATUS_ERROR, 'External API request failed with status: ' . $response->status());
                    throw new \Exception('External API request failed with status: ' . $response->status());
                }

                $data = $response->json();
                $apiSpan->setAttribute('http.status_code', $response->status());
                $apiSpan->setStatus(StatusCode::STATUS_OK);
            } catch (\Exception $e) {
                $apiSpan->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());
                throw $e;
            } finally {
                $apiSpan->end();
            }

            $span->setAttribute('http.status_code', Response::HTTP_OK);
            $span->setStatus(StatusCode::STATUS_OK);

            return response()->json($data);
        } catch (\Exception $e) {
            $span->setAttribute('http.status_code', Response::HTTP_INTERNAL_SERVER_ERROR);
            $span->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());
            throw $e;
        } finally {
            $span->end();
        }
    }
}
