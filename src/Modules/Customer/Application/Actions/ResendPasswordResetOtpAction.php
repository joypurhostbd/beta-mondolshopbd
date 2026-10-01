<?php

namespace Modules\Customer\Application\Actions;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Domain\Contracts\SmsServiceInterface;
use Modules\Customer\Domain\Events\CustomerPasswordResetRequestedEvent;
use Shared\Domain\Exceptions\EntityNotFoundException;

class ResendPasswordResetOtpAction
{
    public function __construct(
        private SmsServiceInterface $smsService
    ) {}

    public function execute(int|string $customerId): int
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            throw EntityNotFoundException::forEntity('Customer', $customerId);
        }

        $otp = rand(111111, 999999);

        DB::transaction(function () use ($customer, $otp) {
            $customer->forgot = $otp;
            $customer->save();
        });

        $this->smsService->sendOtp($customer->phone, $otp);

        event(new CustomerPasswordResetRequestedEvent($customer->id, $customer->phone, $otp));

        return $otp;
    }
}