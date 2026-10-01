<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Anyone can view categories.
     */
    public function viewAny($user = null): bool
    {
        return true;
    }

    /**
     * Anyone can view a specific category.
     */
    public function view($user = null, ?Category $category = null): bool
    {
        return true;
    }

    /**
     * Determine whether the admin can create categories.
     */
    public function create(User $user): bool
    {
        return $user->can('category-create');
    }

    /**
     * Determine whether the admin can update the category.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->can('category-edit');
    }

    /**
     * Determine whether the admin can delete the category.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->can('category-delete');
    }
}