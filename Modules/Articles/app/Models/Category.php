<?php

namespace Modules\Articles\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Articles\Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'created_by'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

   public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_category')
                    ->using(ArticleCategory::class) // opcional, se usar o modelo pivot
                    ->withTimestamps();
    }

     public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
