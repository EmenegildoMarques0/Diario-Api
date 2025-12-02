<?php

namespace Modules\Articles\app\Policies;

use App\Models\User;
use Modules\Articles\app\Models\Article;

class ArticlePolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['editor', 'admin']);
    }

    public function update(User $user, Article $article): bool
    {
        return $user->id === $article->author_id || $user->role === 'admin';
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->role === 'admin';
    }

    public function publish(User $user, Article $article): bool
    {
        return $user->role === 'admin' ||
               ($user->id === $article->author_id && $user->role === 'editor');
    }

    public function restore(User $user, Article $article): bool
    {
        return $user->role === 'admin';
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin('admin'); // ou $user->is_admin, etc.
    }
}
