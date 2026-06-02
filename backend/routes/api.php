<?php

use App\Http\Controllers\Api\HealthRecordController;
use Illuminate\Support\Facades\Route;

/*
|------------------------------------------------------------------------------
| API Routes
|------------------------------------------------------------------------------
*/

Route::get('/ping', fn () => response()->json([
    'service' => 'health-dashboard-api',
    'status' => 'ok',
    'time' => now()->toIso8601String(),
]));

Route::apiResource('health-records', HealthRecordController::class)
    ->only(['index', 'store', 'show']);

// Differential — temporal trend analysis.
Route::post('health-records/{id}/trend-analysis', [HealthRecordController::class, 'trendAnalysis']);
