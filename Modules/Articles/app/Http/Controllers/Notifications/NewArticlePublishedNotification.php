<?php

namespace Modules\Articles\app\Http\Controllers\Notifications;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Articles\app\Models\Article;
use Illuminate\Support\Facades\Log;
use Modules\Articles\app\Emails\NewArticlePublished;

class NewArticlePublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
         Log::info('entrou na notificacao de novo artigo publicado');

        return (new NewArticlePublished($this->article, $notifiable))
                    ->to($notifiable->email);
    }
}
