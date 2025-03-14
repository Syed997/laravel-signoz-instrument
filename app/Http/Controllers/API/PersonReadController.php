<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Http\Resources\PersonResource;
use Illuminate\Http\Response;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\API\Trace\StatusCode;

class PersonReadController extends Controller
{
    protected $tracer;

    public function __construct(Tracer $tracer)
    {
        $this->tracer = $tracer;
    }

    public function index()
    {
        $span = $this->tracer->spanBuilder('GET /api/people')->startSpan();
        try {
            $span->setAttribute('http.method', 'GET');
            $span->setAttribute('http.route', '/api/people');

            $dbSpan = $this->tracer->spanBuilder('DB Fetch')->startSpan();
            $dbSpan->setAttribute('db.system', 'postgres');
            $dbSpan->setAttribute('db.statement', 'SELECT * FROM people');

            try {
                $people = Person::all();
                $response = PersonResource::collection($people);

                $dbSpan->setStatus(StatusCode::STATUS_OK);
            } catch (\Exception $e) {
                $dbSpan->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());
                throw $e;
            } finally {
                $dbSpan->end();
            }

            $span->setAttribute('http.status_code', Response::HTTP_OK);
            $span->setStatus(StatusCode::STATUS_OK);

            return $response;
        } catch (\Exception $e) {
            $span->setAttribute('http.status_code', Response::HTTP_INTERNAL_SERVER_ERROR);
            $span->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());
            throw $e;
        } finally {
            $span->end();
        }
    }

    public function show(string $id)
    {
        $span = $this->tracer->spanBuilder('GET /api/people/{id}')->startSpan();
        try {
            $span->setAttribute('http.method', 'GET');
            $span->setAttribute('http.route', '/api/people/{id}');
            $span->setAttribute('person.id', $id);

            $dbSpan = $this->tracer->spanBuilder('DB Fetch')->startSpan();
            $dbSpan->setAttribute('db.system', 'postgres');
            $dbSpan->setAttribute('db.statement', 'SELECT * FROM people WHERE id = ?');

            try {
                $person = Person::findOrFail($id);
                $dbSpan->setStatus(StatusCode::STATUS_OK);
            } catch (\Exception $e) {
                $dbSpan->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());
                throw $e;
            } finally {
                $dbSpan->end();
            }

            $span->setAttribute('http.status_code', Response::HTTP_OK);
            $span->setStatus(StatusCode::STATUS_OK);
            return new PersonResource($person);
        } catch (\Exception $e) {
            $span->setAttribute('http.status_code', Response::HTTP_NOT_FOUND);
            $span->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());
            throw $e;
        } finally {
            $span->end();
        }
    }
}
