<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\app\Http\Controllers\OverviewController;
use Modules\Dashboard\Http\Controllers\DashboardController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Dashboard geral
    Route::apiResource('dashboards', DashboardController::class)->names('dashboard');

    // === ROTAS DO ADMIN (agrupadas com prefixo /admin) ===
    Route::prefix('admin')->name('admin.')->group(function () {

        // Overview do módulo Articles
        Route::get('dashboard/overview', [OverviewController::class, 'show'])
            ->name('articles.overview');
    });
});
