<?php

namespace Modules\Articles\app\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Articles\Database\Factories\ArticleCategoryFactory;

class ArticleCategory extends Model
{
    protected $table = 'article_category';

    public $timestamps = true;
}
