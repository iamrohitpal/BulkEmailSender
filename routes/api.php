<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test-route', function () {
    return response()->json([
        'message' => 'Route is working!',
        'data' => [
            'key' => 'value',
            'number' => 123
        ]
    ]);
});