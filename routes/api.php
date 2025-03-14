<?php

use App\Http\Controllers\API\ExternalApiController;
use App\Http\Controllers\API\PersonController;
use App\Http\Controllers\API\PersonReadController;
use App\Http\Controllers\API\PersonWriteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('log.api')->group(function () {
    // Route::apiResource('people', PersonController::class);
    // Route::delete('people/truncate', [PersonController::class, 'truncate'])->name('people.truncate');
    // Route::get('external/people', [ExternalApiController::class, 'fetchPeople'])->name('external.people');
    Route::get('/people', [PersonReadController::class, 'index']);
    Route::get('/people/{id}', [PersonReadController::class, 'show']);
    Route::post('/people', [PersonWriteController::class, 'store']);
    Route::put('/people/{id}', [PersonWriteController::class, 'update']);
    Route::delete('/people/{id}', [PersonWriteController::class, 'destroy']);
});
