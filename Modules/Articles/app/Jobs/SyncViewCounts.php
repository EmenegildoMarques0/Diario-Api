<?php

namespace Modules\Articles\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Articles\app\Models\Article;

class SyncViewCounts implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public function handle(): void
    {
        // Exemplo: pode sincronizar view_count com dados externos
        Article::where('view_count', '>', 0)->each(function ($article) {
            \Log::info("Sincronizando views do artigo {$article->id}: {$article->view_count}");
        });
    }
}
