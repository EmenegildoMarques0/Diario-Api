<?php

namespace Modules\Articles\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ArticleImage extends Model
{
   protected $fillable = [
        'article_id',
        'path',
        'is_cover',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function scopeCover($query)
    {
        return $query->where('is_cover', true);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
