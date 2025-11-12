<?php

use Illuminate\Support\Facades\Route;
use Modules\Articles\app\Http\Controllers\Admin\ArticleController as AdminArticleController;
use Modules\Articles\app\Http\Controllers\ArticlesController;
use Modules\Articles\app\Http\Controllers\CategoryController;

Route::prefix('v1')->group(function () {
    // Rotas públicas de artigos
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticlesController::class, 'index'])->name('articles.index');
        Route::get('/{article:slug}', [ArticlesController::class, 'show'])->name('articles.show');
    });

    // Rotas administrativas protegidas por autenticação
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('admin')->group(function () {
            // Artigos
            Route::apiResource('articles', AdminArticleController::class)
                ->parameters(['articles' => 'article:slug'])
                ->names('admin.articles');

            Route::post('articles/{article:slug}/restore', [AdminArticleController::class, 'restore'])
                ->name('admin.articles.restore');

            Route::post('articles/{article:slug}/categories', [AdminArticleController::class, 'attachCategory'])
                ->name('admin.articles.attach-category');

            Route::delete('articles/{article:slug}/categories', [AdminArticleController::class, 'detachCategory'])
                ->name('admin.articles.detach-category');

            // Categorias
            Route::apiResource('categories', CategoryController::class)
                ->parameters(['categories' => 'category:slug'])
                ->names('admin.categories');
        });
    });
});
