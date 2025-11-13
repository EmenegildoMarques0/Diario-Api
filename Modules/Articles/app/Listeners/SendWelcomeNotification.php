<?php

namespace Modules\Articles\app\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Articles\app\Events\UserRegistered;
use Modules\Articles\app\Http\Controllers\Notifications\WelcomeNotificationController;

class SendWelcomeNotification implements ShouldQueue
{
    public function handle(UserRegistered $event): void
    {
        $event->user->notify(new WelcomeNotificationController());
    }
}
