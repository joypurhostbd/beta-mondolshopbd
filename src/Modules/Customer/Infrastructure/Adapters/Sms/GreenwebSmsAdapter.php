<?php

namespace Modules\Customer\Infrastructure\Adapters\Sms;

use Illuminate\Support\Facades\Http;
use Modules\Customer\Domain\Contracts\SmsServiceInterface;
use Shared\Domain\ValueObjects\PhoneNumber;

class GreenwebSmsAdapter implements SmsServiceInterface
{
    public function __construct(
        private ?string $token = null
    ) {}

    public function send(string $recipientPhone, string $message): bool
    {
        try {
            $normalized = PhoneNumber::from($recipientPhone)->getValue();
            $token = $this->token ?: config('services.greenweb.token', 'test_token');

            $response = Http::asForm()->timeout(5)->post('https://api.greenweb.com.bd/api.php', [
                'token' => $token,
                'to' => $normalized,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    public function sendOtp(string $recipientPhone, int $otp): bool
    {
        $message = "Your MondolShopBD OTP is {$otp}. Valid for 5 minutes.";
        return $this->send($recipientPhone, $message);
    }
}