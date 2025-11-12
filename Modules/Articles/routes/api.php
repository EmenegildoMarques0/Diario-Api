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
    Route::middleware('auth:sanctum')->group(function () {
        // Artigos (privados)
        Route::prefix('admin')->group(function () {
            Route::apiResource('articles', AdminArticleController::class)->parameters([
                'articles' => 'article'
            ])->names('admin.articles');
            Route::post('articles/{id}/restore', [AdminArticleController::class, 'restore'])->name('admin.articles.restore');
        });

        // Categorias (privadas)
        Route::prefix('admin')->group(function () {
            Route::apiResource('categories', CategoryController::class)->parameters([
                'categories' => 'category'
            ])->names('admin.categories');
        });
    });
});
