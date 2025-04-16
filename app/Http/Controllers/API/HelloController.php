<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class HelloController extends Controller
{
    public function index()
    {
        Log::info('Hello API called', [
            'timestamp' => now()->toDateTimeString(),
            'endpoint' => 'api/hello'
        ]);

        usleep(100000);

        Log::info('Hello API responding', [
            'message' => 'Hello World',
            'delay_ms' => 100
        ]);

        return response()->json(['message' => 'Hello World']);
    }
}
