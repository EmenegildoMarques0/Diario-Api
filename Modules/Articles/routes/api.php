<?php

use Illuminate\Support\Facades\Route;
use Modules\Articles\app\Http\Controllers\Admin\ArticleController as AdminArticleController;
use Modules\Articles\app\Http\Controllers\ArticlesController;
use Modules\Articles\app\Http\Controllers\CategoryController;
use Modules\Articles\app\Http\Controllers\Notifications\NewsletterController;
use Modules\Articles\app\Http\Controllers\Notifications\NotificationController;

// IMPORTANTE: adicione o controller da newsletter

Route::prefix('v1')->group(function () {

    // Rotas públicas de artigos
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticlesController::class, 'index'])->name('articles.index');
        Route::get('/featured', [ArticlesController::class, 'featured'])->name('articles.featured');
        Route::get('/{article:slug}', [ArticlesController::class, 'show'])->name('articles.show');
    });

    // =============================================
    // ROTAS PÚBLICAS DA NEWSLETTER (qualquer um pode se inscrever/cancelar)
    // =============================================
    Route::prefix('newsletter')->group(function () {
        Route::post('subscribe', [NewsletterController::class, 'subscribe'])
            ->name('newsletter.subscribe');

        Route::get('unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])
            ->name('newsletter.unsubscribe');
    });

    // =============================================
    // ROTAS PROTEGIDAS (autenticadas com Sanctum)
    // =============================================
    Route::middleware(['auth:sanctum'])->group(function () {

        // Rotas administrativas (painel admin)
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

            // =====================================
            // ROTAS ADMIN DA NEWSLETTER (listar, stats, etc)
            // =====================================
            Route::prefix('newsletter')->group(function () {
                Route::get('subscribers', [NewsletterController::class, 'index'])
                    ->name('admin.newsletter.subscribers');

                Route::post('send-article', [NewsletterController::class, 'sendArticleAsNewsletter']);
                Route::get('sent-articles', [NewsletterController::class, 'sentArticlesLog'])->name('admin.newsletter.sent-articles');
                Route::get('subscribers/stats', [NewsletterController::class, 'stats'])
                    ->name('admin.newsletter.stats');
            });
        });

        // Rotas de notificações (usuários autenticados normais)
        Route::prefix('auth')->group(function () {
            Route::get('notifications', [NotificationController::class, 'index']);
            Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
            Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
            Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        });
    });
});
