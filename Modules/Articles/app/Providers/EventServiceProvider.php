<?php

namespace Modules\Articles\app\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Articles\app\Events\ArticlePublished;
use Modules\Articles\app\Events\UserRegistered;
use Modules\Articles\app\Listeners\SendArticlePublishedNotification;
use Modules\Articles\app\Listeners\SendWelcomeNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserRegistered::class => [
            SendWelcomeNotification::class,
        ],
        \Modules\Articles\app\Events\ArticleReadyForNewsletter::class => [
        \Modules\Articles\app\Listeners\SendArticleToSubscribers::class,
    ],

    ];

    // IMPORTANTE: Desativar descoberta automática
    protected static $shouldDiscoverEvents = false;

    public function boot(): void
    {
        parent::boot();
    }
}
