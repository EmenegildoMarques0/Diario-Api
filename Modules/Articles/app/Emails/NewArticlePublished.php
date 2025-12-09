<?php

namespace Modules\Articles\app\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Articles\app\Models\Article;

class NewArticlePublished extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $article;
    public $user;

    public function __construct(Article $article, $user)
    {
        $this->article = $article;
        $this->user = $user;
    }

    public function build()
    {
         Log::info('entrou no email de novo artigo publicado');

        return $this->subject("Novo artigo: {$this->article->title}")
                    ->markdown('articles::emails.articles.new')
                    ->with([
                        'article' => $this->article,
                        'user' => $this->user,
                    ]);
                     // Aponta para o template Markdown

    }
}
