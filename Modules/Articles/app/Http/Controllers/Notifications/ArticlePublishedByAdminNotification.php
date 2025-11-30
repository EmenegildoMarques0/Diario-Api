<?php

namespace Modules\Articles\app\Http\Controllers\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Articles\app\Models\Article;
use App\Models\User;

class ArticlePublishedByAdminNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Article $article,
        public User $admin
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

   public function toDatabase($notifiable): array
{
    return [
        'message'      => "Seu artigo \"{$this->article->title}\" foi publicado pelo administrador {$this->admin->name}.",
        'type'         => 'article_published_by_admin',
        'article_id'   => $this->article->id,
        'article_title'=> $this->article->title,
        'admin_name'   => $this->admin->name,
        'action_url'   => url("/v1/articles/{$this->article->slug}"),
        'published_at' => $this->article->published_at->format('d/m/Y H:i'),
    ];
}
}
