<?php

namespace Modules\Articles\app\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;

class WelcomeNotificationController extends Notification
{
    use Queueable;

    public function __construct() {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => "Bem-vindo, {$notifiable->name}! Obrigado por se juntar à nossa plataforma.",
            'type' => 'welcome',
            'action_url' => url('/dashboard'),


        ];
    }
}
