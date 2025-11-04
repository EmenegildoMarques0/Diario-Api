<?php

use Illuminate\Support\Facades\Route;
use Modules\Articles\app\Http\Controllers\Admin\ArticleController as AdminArticleController;
use Modules\Articles\app\Http\Controllers\ArticlesController;

Route::prefix('v1')->group(function () {

    // Rotas públicas de artigos
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticlesController::class, 'index']);
        Route::get('/{article:slug}', [ArticlesController::class, 'show']);
    });

    // Rotas administrativas protegidas por autenticação
    Route::middleware('auth:sanctum')->prefix('admin/articles')->group(function () {
        Route::apiResource('/', AdminArticleController::class)->parameters([
            '' => 'article'
        ]);
        Route::post('/{id}/restore', [AdminArticleController::class, 'restore']);
    });
});
