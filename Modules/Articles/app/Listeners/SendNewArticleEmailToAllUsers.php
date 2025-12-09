<?php

namespace Modules\Articles\app\Listeners;


use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Articles\app\Events\ArticlePublished;
use Modules\Articles\app\Http\Controllers\Notifications\NewArticlePublishedNotification;

class SendNewArticleEmailToAllUsers
{
    use InteractsWithQueue;

    public function handle(ArticlePublished $event)
    {
        $article = $event->article;

         Log::info('entrou no listener de novo artigo publicado');

        User::query()
            ->chunk(100, function ($users) use ($article) {
                foreach ($users as $user) {
                    try {
                        $user->notify(new NewArticlePublishedNotification($article));
                    } catch (\Exception $e) {
                        Log::error('Falha ao notificar usuário sobre novo artigo', [
                            'user_id' => $user->id,
                            'article_id' => $article->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }
}
