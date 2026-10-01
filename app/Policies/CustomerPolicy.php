<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the customer profile.
     */
    public function view($user, Customer $customer): bool
    {
        if ($user instanceof Customer) {
            return (int) $user->id === (int) $customer->id;
        }

        if ($user instanceof User) {
            return $user->can('customer-list') || $user->can('customer-edit');
        }

        return false;
    }

    /**
     * Determine whether the user can update the customer profile.
     */
    public function update($user, Customer $customer): bool
    {
        if ($user instanceof Customer) {
            return (int) $user->id === (int) $customer->id;
        }

        if ($user instanceof User) {
            return $user->can('customer-edit');
        }

        return false;
    }
}