<?php

namespace Modules\Customer\Application\Actions;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Shared\Domain\Exceptions\EntityNotFoundException;

class ResetCustomerPasswordAction
{
    public function execute(int|string $customerId, int|string $otp, string $newPassword): bool
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            throw EntityNotFoundException::forEntity('Customer', $customerId);
        }

        if (empty($customer->forgot) || (string) $customer->forgot !== (string) $otp) {
            throw new InvalidArgumentException("Invalid or expired OTP.");
        }

        return DB::transaction(function () use ($customer, $newPassword) {
            $customer->password = Hash::make($newPassword);
            $customer->forgot = null; // Invalidate OTP after use
            return $customer->save();
        });
    }
}