<?php

namespace Modules\Articles\app\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Articles\app\Events\ArticlePublished;
use Modules\Articles\app\Http\Controllers\Notifications\ArticlePublishedNotificationController;

class SendArticlePublishedNotification implements ShouldQueue
{
    public function handle(ArticlePublished $event): void
    {
        // Notifica o autor do artigo
        $event->article->author->notify(
            new ArticlePublishedNotificationController($event->article)
        );

        // Notifica administradores
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new ArticlePublishedNotificationController($event->article));
        }
    }
}
