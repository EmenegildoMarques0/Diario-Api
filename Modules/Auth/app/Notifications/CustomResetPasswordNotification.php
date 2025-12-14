<?php

namespace Modules\Auth\app\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPasswordNotification extends ResetPasswordNotification
{
    /**
     * Construa a representação da notificação.
     *
     * @param  mixed  $notifiable (Este é o objeto User)
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        // 1. Obtém o URL Base do seu Front-end/Cliente do arquivo .env
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

        // 2. Constrói o link completo (URL que será passado para a view)
        $url = $frontendUrl .
               '/reset-password?token=' . $this->token .
               '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Sua Solicitação de Redefinição de Senha')
            // Usa o método view() para carregar o seu template HTML customizado
            ->view(
                'auth::emails.auth.password-reset', // <-- Nome do arquivo blade que criamos
                [
                    'url' => $url,           // Passa a URL para o botão na view
                    'user' => $notifiable    // Passa o objeto User para a saudação
                ]
            );
    }
}
