<?php

namespace Modules\Articles\app\Observers;

use Modules\Articles\app\Models\Article;
use Illuminate\Support\Str;

class ArticleObserverObserver
{
    public function creating(Article $article): void
    {
        $article->author_id = auth()->id();
        if (empty($article->slug)) {
            $article->slug = Str::slug($article->title);
        }
    }

    public function updating(Article $article): void
    {
        if ($article->isDirty('title') && empty($article->getOriginal('slug'))) {
            $article->slug = Str::slug($article->title);
        }
    }
}
