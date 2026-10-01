<?php

namespace Modules\Customer\Infrastructure\Adapters\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Domain\Contracts\SmsServiceInterface;
use Shared\Domain\ValueObjects\PhoneNumber;

class JoypurHostSmsAdapter implements SmsServiceInterface
{
    public const DEFAULT_URL = 'https://sms.joypurhost.com/api/smsapi';
    public const BALANCE_URL = 'https://sms.joypurhost.com/api/getBalanceApi';

    public const RESPONSE_CODES = [
        202 => 'SMS Submitted Successfully',
        1001 => 'Invalid Number',
        1002 => 'Sender ID not correct or disabled',
        1003 => 'Required fields missing',
        1005 => 'Internal Error',
        1006 => 'Balance Validity Not Available',
        1007 => 'Balance Insufficient',
        1011 => 'User ID not found',
        1012 => 'Masking SMS must be sent in Bengali',
        1013 => 'Sender ID not found for this API key',
        1014 => 'Sender type name not found for this API key',
        1015 => 'No valid gateway found for this API key',
        1016 => 'Active price info not found for this sender ID',
        1017 => 'Price info not found for this sender ID',
        1018 => 'Owner account is disabled',
        1019 => 'Price for this account is disabled',
        1020 => 'Parent account not found',
        1021 => 'Parent active price not found',
        1031 => 'Account not verified',
        1032 => 'IP not whitelisted',
    ];

    public function __construct(
        private ?string $apiKey = null,
        private ?string $senderId = null,
        private ?string $url = null
    ) {}

    public function send(string $recipientPhone, string $message): bool
    {
        try {
            $normalized = PhoneNumber::from($recipientPhone)->getValue();
            $formattedNumber = '88' . $normalized;
            $url = $this->url ?: self::DEFAULT_URL;
            $apiKey = $this->apiKey ?: (string) config('services.joypurhost_sms.api_key', '');
            $senderId = $this->senderId ?: (string) config('services.joypurhost_sms.sender_id', '');

            $response = Http::asForm()->timeout(15)->post($url, [
                'api_key' => $apiKey,
                'senderid' => $senderId,
                'number' => $formattedNumber,
                'message' => $message,
            ]);

            $body = trim($response->body());

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && isset($data['response_code']) && (int) $data['response_code'] === 202) {
                    return true;
                }

                if ($body === '202' || str_contains($body, '202') || str_contains(strtolower($body), 'success')) {
                    return true;
                }

                $code = is_numeric($body) ? (int) $body : (is_array($data) && isset($data['response_code']) ? (int) $data['response_code'] : null);
                $reason = $code && isset(self::RESPONSE_CODES[$code]) ? self::RESPONSE_CODES[$code] : $body;

                Log::warning('JoypurHost SMS rejected response', [
                    'phone' => $formattedNumber,
                    'code' => $code,
                    'reason' => $reason,
                ]);

                return false;
            }

            Log::warning('JoypurHost SMS HTTP failure', [
                'phone' => $formattedNumber,
                'status' => $response->status(),
                'response' => $body,
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::warning('JoypurHost SMS exception: ' . $e->getMessage());
            return false;
        }
    }

    public function sendOtp(string $recipientPhone, int $otp): bool
    {
        $message = "Your MondolShopBD OTP is {$otp}. Valid for 5 minutes.";
        return $this->send($recipientPhone, $message);
    }

    public function getBalance(): ?string
    {
        try {
            $apiKey = $this->apiKey ?: (string) config('services.joypurhost_sms.api_key', '');
            if (empty($apiKey)) {
                return null;
            }

            $response = Http::asForm()->timeout(10)->post(self::BALANCE_URL, [
                'api_key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    if (isset($data['response_code']) && (int) $data['response_code'] === 202 && isset($data['balance'])) {
                        return (string) $data['balance'];
                    }
                    if (isset($data['balance'])) {
                        return (string) $data['balance'];
                    }
                    return null;
                }

                $body = trim($response->body());
                if (is_numeric($body) && isset(self::RESPONSE_CODES[(int) $body]) && (int) $body !== 202) {
                    return null;
                }
                if (is_numeric($body)) {
                    return $body;
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('JoypurHost SMS getBalance exception: ' . $e->getMessage());
            return null;
        }
    }

    public function testConnection(): array
    {
        $balance = $this->getBalance();
        if ($balance !== null) {
            return [
                'success' => true,
                'current_balance' => $balance,
                'message' => "Connected successfully to JoypurHost SMS! Current Balance: {$balance} SMS",
            ];
        }

        return [
            'success' => false,
            'current_balance' => null,
            'message' => 'Connection to JoypurHost SMS failed. Please verify your API Key and URL.',
        ];
    }
}
