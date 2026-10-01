<?php

namespace Modules\Customer\Infrastructure\Adapters\Sms;

use Illuminate\Support\Facades\Http;
use Modules\Customer\Domain\Contracts\SmsServiceInterface;
use Shared\Domain\ValueObjects\PhoneNumber;

class AlphaSmsAdapter implements SmsServiceInterface
{
    public function __construct(
        private ?string $apiKey = null
    ) {}

    public function send(string $recipientPhone, string $message): bool
    {
        try {
            $normalized = PhoneNumber::from($recipientPhone)->getValue();
            $apiKey = $this->apiKey ?: config('services.alpha_sms.api_key', 'test_key');

            $response = Http::asForm()->timeout(5)->post('https://api.sms.net.bd/sendsms', [
                'api_key' => $apiKey,
                'msg' => $message,
                'to' => $normalized,
            ]);

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    public function sendOtp(string $recipientPhone, int $otp): bool
    {
        $message = "MondolShopBD OTP is {$otp}.";
        return $this->send($recipientPhone, $message);
    }
}