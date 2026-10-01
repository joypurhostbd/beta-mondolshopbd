<?php

namespace Modules\Customer\Application\Actions;

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Shared\Domain\Exceptions\EntityNotFoundException;

class ChangeCustomerPasswordAction
{
    public function execute(int|string $customerId, string $oldPassword, string $newPassword): bool
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            throw EntityNotFoundException::forEntity('Customer', $customerId);
        }

        if (!Hash::check($oldPassword, $customer->password)) {
            throw new InvalidArgumentException("Current password does not match.");
        }

        $customer->password = Hash::make($newPassword);
        return $customer->save();
    }
}