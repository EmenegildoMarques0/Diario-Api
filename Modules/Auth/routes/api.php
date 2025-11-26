<?php


use Illuminate\Support\Facades\Route;
use Modules\Auth\app\Http\Controllers\{AuthController,ProfileController, UserManagementController, UserPromotionController};


Route::prefix('v1')->group(function () {

    // Rotas públicas
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    // Rotas protegidas por autenticação
    Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::post('users/{userId}/role', [UserPromotionController::class, 'updateRole']);
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::get('/users/{userId}', [UserManagementController::class, 'show']);
        Route::delete('/users/{userId}', [UserManagementController::class, 'destroy']);
        Route::post('/users/{userId}/restore', [UserManagementController::class, 'restore']);
        Route::get('validate-token', [AuthController::class, 'validateToken']);
    });
});
