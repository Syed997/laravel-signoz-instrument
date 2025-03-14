<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Http\Resources\PersonResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\API\Trace\StatusCode;

class PersonWriteController extends Controller
{
    protected $tracer;

    public function __construct(Tracer $tracer)
    {
        $this->tracer = $tracer;
    }

    public function store(Request $request)
    {
        $span = $this->tracer->spanBuilder('POST /api/people')->startSpan();
        try {
            $span->setAttribute('http.method', 'POST');
            $span->setAttribute('http.route', '/api/people');

            $dbSpan = $this->tracer->spanBuilder('DB Insert')->startSpan();
            $dbSpan->setAttribute('db.system', 'postgres');
            $dbSpan->setAttribute('db.statement', 'INSERT INTO people (name, age) VALUES (?, ?)');

            try {
                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'age' => 'required|integer|min:0',
                ]);

                $person = Person::create($validated);

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
            $span->setAttribute('http.status_code', Response::HTTP_INTERNAL_SERVER_ERROR);
            $span->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());
            throw $e;
        } finally {
            $span->end();
        }
    }

    public function update(Request $request, string $id)
    {
        $span = $this->tracer->spanBuilder('PUT /api/people/{id}')->startSpan();
        try {
            $span->setAttribute('http.method', 'PUT');
            $span->setAttribute('http.route', '/api/people/{id}');
            $span->setAttribute('person.id', $id);

            $dbSpan = $this->tracer->spanBuilder('DB Update')->startSpan();
            $dbSpan->setAttribute('db.system', 'postgres');
            $dbSpan->setAttribute('db.statement', 'UPDATE people SET name = ?, age = ? WHERE id = ?');

            try {
                $person = Person::findOrFail($id);

                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'age' => 'required|integer|min:0',
                ]);

                $person->update($validated);

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

    public function destroy(string $id)
    {
        $span = $this->tracer->spanBuilder('DELETE /api/people/{id}')->startSpan();
        try {
            $span->setAttribute('http.method', 'DELETE');
            $span->setAttribute('http.route', '/api/people/{id}');
            $span->setAttribute('person.id', $id);

            $dbSpan = $this->tracer->spanBuilder('DB Delete')->startSpan();
            $dbSpan->setAttribute('db.system', 'postgres');
            $dbSpan->setAttribute('db.statement', 'DELETE FROM people WHERE id = ?');

            try {
                $person = Person::findOrFail($id);
                $person->delete();

                $dbSpan->setStatus(StatusCode::STATUS_OK);
            } catch (\Exception $e) {
                $dbSpan->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());
                throw $e;
            } finally {
                $dbSpan->end();
            }

            $span->setAttribute('http.status_code', Response::HTTP_NO_CONTENT);
            $span->setStatus(StatusCode::STATUS_OK);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            $span->setAttribute('http.status_code', Response::HTTP_NOT_FOUND);
            $span->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());
            throw $e;
        } finally {
            $span->end();
        }
    }
}
