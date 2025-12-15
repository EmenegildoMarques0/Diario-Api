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
        Route::get('/{article:slug}/recommendations', [ArticlesController::class, 'getRecommendations']);
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

            // =============================================
            // ARTIGOS (Implementação de rota individualizada)
            // =============================================
            Route::prefix('articles')->group(function () {

                // Index (GET /v1/admin/articles)
                Route::get('/', [AdminArticleController::class, 'index'])
                    ->name('admin.articles.index');

                // Store (POST /v1/admin/articles)
                Route::post('/', [AdminArticleController::class, 'store'])
                    ->name('admin.articles.store');

                // Show (GET /v1/admin/articles/{article:slug})
                Route::get('{article:slug}', [AdminArticleController::class, 'show'])
                    ->name('admin.articles.show');

                // Update (PUT/PATCH /v1/admin/articles/{article:slug})
                Route::match(['put', 'patch', 'post'], '{article:slug}', [AdminArticleController::class, 'update'])
                    ->name('admin.articles.update');

                // Destroy (DELETE /v1/admin/articles/{article:slug})
                Route::delete('{article:slug}', [AdminArticleController::class, 'destroy'])
                    ->name('admin.articles.destroy');

                // Restore (POST /v1/admin/articles/{article:slug}/restore)
                // Mantém o binding por slug para consistência com o restante
                Route::post('{article:slug}/restore', [AdminArticleController::class, 'restore'])
                    ->name('admin.articles.restore');

                // Attach Category (POST /v1/admin/articles/{article:slug}/categories)
                Route::post('{article:slug}/categories', [AdminArticleController::class, 'attachCategory'])
                    ->name('admin.articles.attach-category');

                // Detach Category (DELETE /v1/admin/articles/{article:slug}/categories)
                // Nota: Usar o método HTTP DELETE para desanexar é semanticamente correto.
                Route::delete('{article:slug}/categories', [AdminArticleController::class, 'detachCategory'])
                    ->name('admin.articles.detach-category');

            });
            // FIM: Artigos

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
