<?php

namespace Modules\Customer\Application\Actions;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Domain\Contracts\SmsServiceInterface;
use Modules\Customer\Domain\Events\CustomerPasswordResetRequestedEvent;
use Shared\Domain\Exceptions\EntityNotFoundException;
use Shared\Domain\ValueObjects\PhoneNumber;

class RequestPasswordResetAction
{
    public function __construct(
        private SmsServiceInterface $smsService
    ) {}

    public function execute(string $phone): int|string
    {
        $normalizedPhone = PhoneNumber::from($phone)->getValue();

        $customer = Customer::where('phone', $normalizedPhone)->first();
        if (!$customer) {
            throw EntityNotFoundException::forEntity('Customer with phone', $normalizedPhone);
        }

        $otp = rand(111111, 999999);

        DB::transaction(function () use ($customer, $otp) {
            $customer->forgot = $otp;
            $customer->save();
        });

        $this->smsService->sendOtp($customer->phone, $otp);

        event(new CustomerPasswordResetRequestedEvent($customer->id, $customer->phone, $otp));

        return $customer->id;
    }
}