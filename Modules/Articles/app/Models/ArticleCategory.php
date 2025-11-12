<?php

namespace Modules\Articles\app\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Articles\Database\Factories\ArticleCategoryFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
class ArticleCategory extends Pivot
{
    protected $table = 'article_category';

    public $timestamps = true;

    // Relacionamentos (opcional, se precisar acessar article ou category diretamente)
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
