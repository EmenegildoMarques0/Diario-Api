<?php

namespace Modules\Articles\app\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Articles\app\Models\Article;

class ArticlePublished
{
    use Dispatchable, SerializesModels;

    public $article;

    public function __construct(Article $article)
    {
         Log::info('entrou no evento de novo artigo publicado');

        $this->article = $article;
    }
}
