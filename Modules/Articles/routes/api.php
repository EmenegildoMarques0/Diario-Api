<?php

use Illuminate\Support\Facades\Route;
use Modules\Articles\app\Http\Controllers\Admin\ArticleController as AdminArticleController;
use Modules\Articles\app\Http\Controllers\ArticlesController;
use Modules\Articles\app\Http\Controllers\CategoryController;
use Modules\Articles\Http\Controllers\S\NotificationController;

Route::prefix('v1')->group(function () {
    // Rotas públicas de artigos
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticlesController::class, 'index'])->name('articles.index');
        Route::get('/{article:slug}', [ArticlesController::class, 'show'])->name('articles.show');
    });

    // Rotas protegidas por autenticação
    Route::middleware(['auth:sanctum'])->group(function () {
        // Rotas administrativas
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

        // Rotas de notificações (acessíveis para usuários autenticados)
        Route::prefix('auth')->group(function () {
            Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
            Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
            Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        });
    });
});
