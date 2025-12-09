<?php

namespace Modules\Articles\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id', 'published_by', 'slug', 'title', 'excerpt', 'content',
        'is_published', 'is_featured', 'view_count', 'published_at'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    // === RELAÇÕES ===
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function images()
    {
        return $this->hasMany(ArticleImage::class)->orderBy('sort_order');
    }

    public function coverImage()
    {
        return $this->hasOne(ArticleImage::class)->where('is_cover', true);
    }

    // === SCOPES ===
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // === ATRIBUTOS ===
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function incrementViewCount()
    {
        $this->increment('view_count');
        \Modules\Articles\app\Events\ArticleViewed::dispatch($this);
    }

  public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'article_category', 'article_id',
        'category_id')
                    ->using(ArticleCategory::class) // opcional
                    ->withTimestamps();
    }
}
