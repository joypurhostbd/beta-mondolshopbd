<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Anyone can view products.
     */
    public function viewAny($user = null): bool
    {
        return true;
    }

    /**
     * Anyone can view a specific product.
     */
    public function view($user = null, ?Product $product = null): bool
    {
        return true;
    }

    /**
     * Determine whether the admin can create products.
     */
    public function create(User $user): bool
    {
        return $user->can('product-create');
    }

    /**
     * Determine whether the admin can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->can('product-edit');
    }

    /**
     * Determine whether the admin can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->can('product-delete');
    }
}