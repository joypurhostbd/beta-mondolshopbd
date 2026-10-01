<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the customer or admin can view the order.
     */
    public function view($user, Order $order): bool
    {
        if ($user instanceof Customer) {
            return (int) $user->id === (int) $order->customer_id;
        }

        if ($user instanceof User) {
            return $user->can('order-list') || $user->can('order-edit');
        }

        return false;
    }

    /**
     * Determine whether the admin can update/process the order.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->can('order-edit') || $user->can('order-process');
    }

    /**
     * Determine whether the admin can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->can('order-delete');
    }
}