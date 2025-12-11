<?php

namespace Modules\Articles\app\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Articles\app\Models\Article;
use Modules\Articles\app\Models\NewsletterSubscriber; // Mantido para tipagem e acesso aos dados do inscrito

class ArticleNewsletterMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Article $article;
    // Renomeado de $subscriber para $user para espelhar o Mailable de destino,
    // embora seja um NewsletterSubscriber.
    public NewsletterSubscriber $user;

    /**
     * Cria uma nova instância da mensagem.
     *
     * @param Article $article
     * @param NewsletterSubscriber $subscriber
     */
    public function __construct(Article $article, NewsletterSubscriber $subscriber)
    {
        $this->article = $article;
        $this->user = $subscriber; // Atribui o subscriber ao $this->user
    }

    /**
     * Constrói a mensagem.
     *
     * @return $this
     */
    public function build()
    {
         // Lógica de saudação (Reader Name) movida para o array 'with'
         $readerName = $this->user->name ?? 'leitor(a)';
         $unsubscribeToken = encrypt($this->user->id);

         Log::info('Preparando email de Newsletter no método build() para: ' . $this->user->email);

        return $this->subject("🔔 Novo Artigo: {$this->article->title}")
                    // Usando o template que foi adaptado para HTML/Blade
                    // OBS: Se você estiver usando o template HTML completo,
                    // troque `markdown` por `view` e ajuste o caminho.
                    ->view('articles::emails.articles.article-newsletter')
                    // Se você quer voltar ao Markdown:
                    // ->markdown('articles::emails.article-newsletter')
                    ->with([
                        'article' => $this->article,
                        'subscriber' => $this->user, // Mantendo o objeto subscriber
                        'subscriberEmail' => $this->user->email, // Passa o email diretamente
                        'readerName' => $readerName, // Passa a saudação (como no código antigo)
                        'unsubscribeToken' => $unsubscribeToken,
                    ]);
    }
}
