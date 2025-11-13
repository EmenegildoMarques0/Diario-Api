<?php

namespace Modules\Articles\app\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Modules\Articles\app\Models\Article;

class ArticlePublishedNotificationController extends Notification
{
    use Queueable;

    public function __construct(protected Article $article) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => "O artigo '{$this->article->title}' foi publicado com sucesso.",
            'type' => 'article_published',
            'article_id' => $this->article->id,
            'action_url' => url("/articles/{$this->article->slug}"),
        ];
    }
}
