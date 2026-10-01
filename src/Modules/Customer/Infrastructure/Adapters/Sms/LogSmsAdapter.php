<?php

namespace Modules\Customer\Infrastructure\Adapters\Sms;

use Modules\Customer\Domain\Contracts\SmsServiceInterface;
use Shared\Domain\ValueObjects\PhoneNumber;

class LogSmsAdapter implements SmsServiceInterface
{
    private array $sentMessages = [];

    public function send(string $recipientPhone, string $message): bool
    {
        try {
            $normalized = PhoneNumber::from($recipientPhone)->getValue();
            $this->sentMessages[] = [
                'to' => $normalized,
                'message' => $message,
                'time' => date('Y-m-d H:i:s'),
            ];
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function sendOtp(string $recipientPhone, int $otp): bool
    {
        $message = "Your MondolShopBD verification code is: {$otp}. Please do not share this code.";
        return $this->send($recipientPhone, $message);
    }

    public function getSentMessages(): array
    {
        return $this->sentMessages;
    }
}