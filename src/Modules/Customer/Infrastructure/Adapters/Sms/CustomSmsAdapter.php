<?php

namespace Modules\Customer\Infrastructure\Adapters\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Domain\Contracts\SmsServiceInterface;
use Shared\Domain\ValueObjects\PhoneNumber;

class CustomSmsAdapter implements SmsServiceInterface
{
    public function __construct(
        private ?string $url = null,
        private ?string $apiKey = null,
        private ?string $senderId = null
    ) {}

    public function send(string $recipientPhone, string $message): bool
    {
        try {
            if (empty($this->url)) {
                Log::warning('Custom SMS provider URL not configured');
                return false;
            }

            $normalized = PhoneNumber::from($recipientPhone)->getValue();

            $response = Http::timeout(15)->post($this->url, [
                'api_key' => $this->apiKey,
                'senderid' => $this->senderId,
                'sender_id' => $this->senderId,
                'contacts' => $normalized,
                'to' => $normalized,
                'msg' => $message,
                'message' => $message,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Custom SMS dispatch failed', [
                'url' => $this->url,
                'phone' => $normalized,
                'status' => $response->status(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::warning('Custom SMS dispatch exception: ' . $e->getMessage());
            return false;
        }
    }

    public function sendOtp(string $recipientPhone, int $otp): bool
    {
        $message = "Your MondolShopBD OTP is {$otp}. Valid for 5 minutes.";
        return $this->send($recipientPhone, $message);
    }
}
