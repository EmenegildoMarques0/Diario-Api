<?php

namespace Modules\Articles\app\Policies;

use App\Models\User;
use Modules\Articles\app\Models\Category;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // todos podem ver categorias
    }

    public function view(User $user, Category $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === 'editor' || $user->role === 'admin';
    }

    public function update(User $user, Category $category): bool
    {
        return $user->role === 'admin' || ($user->role === 'editor' && $user->id === $category->created_by);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->role === 'admin';
    }
}
