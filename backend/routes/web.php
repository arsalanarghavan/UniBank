<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'OstadBank API',
        'version' => 'v1',
        'docs' => url('/docs/api'),
    ]);
});
