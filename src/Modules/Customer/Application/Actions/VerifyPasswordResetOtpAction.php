<?php

namespace Modules\Customer\Application\Actions;

use App\Models\Customer;

class VerifyPasswordResetOtpAction
{
    public function execute(int|string $customerId, int|string $otp): bool
    {
        $customer = Customer::find($customerId);
        if (!$customer || empty($customer->forgot)) {
            return false;
        }

        return (string) $customer->forgot === (string) $otp;
    }
}