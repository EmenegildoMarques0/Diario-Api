<?php

namespace Modules\Articles\app\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Articles\app\Models\Article;

class ArticleReadyForNewsletter
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Article $article;
    public int $senderId; // <--- NOVA PROPRIEDADE PARA RASTREAR QUEM ENVIOU

    /**
     * Cria uma nova instância do evento.
     *
     * @param Article $article O artigo a ser enviado.
     * @param int $senderId O ID do usuário que disparou o envio.
     */
    public function __construct(Article $article, int $senderId) // <--- CONSTRUTOR ATUALIZADO
    {
        $this->article = $article;
        $this->senderId = $senderId;
    }
}
