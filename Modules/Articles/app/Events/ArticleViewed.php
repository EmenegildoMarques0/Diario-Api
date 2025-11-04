<?php

namespace Modules\Articles\app\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Articles\app\Models\Article;

class ArticleViewed
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Article $article) {}
}
