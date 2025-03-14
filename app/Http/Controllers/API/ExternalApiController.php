<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class ExternalApiController extends Controller
{
    public function fetchPeople()
    {
        try {
            $response = Http::timeout(10)->get('https://jsonplaceholder.typicode.com/users');

            if (!$response->successful()) {
                throw new \Exception('External API request failed with status: ' . $response->status());
            }

            $data = $response->json();

            return response()->json($data);
        } catch (\Exception $e) {
            throw $e; // Let Laravel's exception handler deal with it
        }
    }
}
