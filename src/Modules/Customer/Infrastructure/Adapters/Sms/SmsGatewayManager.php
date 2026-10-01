<?php

namespace Modules\Customer\Infrastructure\Adapters\Sms;

use App\Models\SmsGateway;
use Modules\Customer\Domain\Contracts\SmsServiceInterface;

class SmsGatewayManager implements SmsServiceInterface
{
    private ?SmsServiceInterface $driver = null;

    public function __construct()
    {
        $this->driver = null;
    }

    public function setDriver(SmsServiceInterface $driver): self
    {
        $this->driver = $driver;
        return $this;
    }

    public function send(string $recipientPhone, string $message): bool
    {
        return $this->getDriver()->send($recipientPhone, $message);
    }

    public function sendOtp(string $recipientPhone, int $otp): bool
    {
        return $this->getDriver()->sendOtp($recipientPhone, $otp);
    }

    public function getActiveDriver(): SmsServiceInterface
    {
        return $this->getDriver();
    }

    private function getDriver(): SmsServiceInterface
    {
        if ($this->driver === null) {
            $this->driver = $this->resolveDriver();
        }

        return $this->driver;
    }

    private function resolveDriver(): SmsServiceInterface
    {
        try {
            $gateway = SmsGateway::where('status', '1')->orWhere('status', 1)->first();
            if (!$gateway) {
                return new LogSmsAdapter();
            }

            $provider = strtolower(trim((string) ($gateway->provider ?? '')));
            $url = strtolower(trim((string) ($gateway->url ?? '')));
            $senderId = $gateway->sender_id ?? $gateway->serderid;

            if ($provider === SmsGateway::PROVIDER_JOYPURHOST || str_contains($url, 'joypurhost') || str_contains($url, 'bulksmsbd')) {
                if ($gateway instanceof \App\Models\SmsProviderJoypurhost) {
                    return $gateway->toAdapter();
                }
                return new JoypurHostSmsAdapter($gateway->api_key, $senderId, $gateway->url);
            }

            if ($provider === SmsGateway::PROVIDER_CUSTOM) {
                return new CustomSmsAdapter($gateway->url, $gateway->api_key, $senderId);
            }

            if (str_contains($url, 'greenweb') || $provider === 'greenweb') {
                return new GreenwebSmsAdapter($gateway->api_key);
            }

            if (str_contains($url, 'sms.net.bd') || str_contains($url, 'alpha') || $provider === 'alpha') {
                return new AlphaSmsAdapter($gateway->api_key);
            }

            if (!empty($gateway->url)) {
                return new CustomSmsAdapter($gateway->url, $gateway->api_key, $senderId);
            }

            return new LogSmsAdapter();
        } catch (\Throwable) {
            return new LogSmsAdapter();
        }
    }
}