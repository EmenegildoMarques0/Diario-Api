<?php

namespace Modules\Articles\app\Listeners;

use Modules\Articles\app\Events\ArticleReadyForNewsletter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log; // Adicionado
use Modules\Articles\app\Models\NewsletterSubscriber;
use Modules\Articles\app\Models\ArticleNewsletterLog; // Adicionado
use Modules\Articles\app\Emails\ArticleNewsletterMail;

class SendArticleToSubscribers implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ArticleReadyForNewsletter $event): void
    {
        $article = $event->article;
        $senderId = $event->senderId; // Captura o ID do usuário que disparou o envio

        // =======================================================
        // 1. VERIFICAÇÃO: Checa se o log já existe (Evita Reenvio)
        // =======================================================
        if (ArticleNewsletterLog::where('article_id', $article->id)->exists()) {
            Log::warning("Artigo ID #{$article->id} ('{$article->title}') JÁ FOI ENVIADO como Newsletter. Abortando envio.");
            return; // Sai do Listener e não processa mais nada
        }

        Log::info("Iniciando envio da Newsletter para o artigo: {$article->title}. Remetente ID: {$senderId}");

        // 2. BUSCAR e ENVIAR o e-mail para cada inscrito ATIVO
        // Usamos cursor() para lidar com grandes volumes sem esgotar a memória
        $subscribers = NewsletterSubscriber::where('is_subscribed', true)->cursor();

        foreach ($subscribers as $subscriber) {
            // Usa o método queue() em vez de send() para garantir que cada Mailable
            // individual também seja processado em segundo plano pela fila.
            Mail::to($subscriber->email)
                ->queue(new ArticleNewsletterMail($article, $subscriber));
        }

        // =======================================================
        // 3. REGISTRO: Loga o envio da Newsletter na tabela
        // (Isso ocorre APÓS o enfileiramento de TODOS os e-mails)
        // =======================================================
        try {
            ArticleNewsletterLog::create([
                'article_id' => $article->id,
                'user_id' => $senderId, // Registra quem disparou o envio
                'sent_at' => now(), // Registra o momento do disparo
            ]);

            Log::info("Log de Newsletter criado com sucesso para o Artigo ID #{$article->id}. Todos os e-mails estão na fila.");

        } catch (\Exception $e) {
            // Trata qualquer exceção que possa ocorrer no banco de dados (ex: problema de conexão)
            Log::error("ERRO FATAL ao registrar Log da Newsletter para o artigo #{$article->id}: " . $e->getMessage());
        }
    }
}
