<?php

use Illuminate\Support\Facades\Route;

/*
|------------------------------------------------------------------------------
| API Routes
|------------------------------------------------------------------------------
| Health-record endpoints are wired in Phase 2. For now a lightweight ping lets
| us confirm the API stack (nginx -> php-fpm -> Laravel) responds on :9000.
*/

Route::get('/ping', fn () => response()->json([
    'service' => 'health-dashboard-api',
    'status' => 'ok',
    'time' => now()->toIso8601String(),
]));
