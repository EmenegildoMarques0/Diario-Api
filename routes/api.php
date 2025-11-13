<?php

use App\Http\Controllers\Debug\DebugController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function () {
    return response()->json(['status' => 'ok'], 200);
});


Route::get('/debug-env', [DebugController::class, 'debugEnv']);
