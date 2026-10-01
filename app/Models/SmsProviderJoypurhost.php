<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Modules\Customer\Infrastructure\Adapters\Sms\JoypurHostSmsAdapter;

class SmsProviderJoypurhost extends SmsGateway
{
    protected $table = 'sms_gateways';
    protected static function booted(): void
    {
        static::addGlobalScope('joypurhost', function (Builder $builder) {
            $builder->where('provider', self::PROVIDER_JOYPURHOST);
        });

        static::creating(function ($model) {
            $model->provider = self::PROVIDER_JOYPURHOST;
            if (empty($model->url)) {
                $model->url = JoypurHostSmsAdapter::DEFAULT_URL;
            }
        });
    }

    public static function getOrCreate(): self
    {
        $senderColumn = static::getSenderIdColumnName();
        $senderDefaults = [
            'url' => JoypurHostSmsAdapter::DEFAULT_URL,
            'provider' => self::PROVIDER_JOYPURHOST,
            'status' => 0,
            'order' => 0,
            'forget_pass' => 0,
            'password_g' => 0,
        ];
        $senderDefaults[$senderColumn] = '';

        return static::firstOrCreate(
            ['provider' => self::PROVIDER_JOYPURHOST],
            $senderDefaults
        );
    }

    public function toAdapter(): JoypurHostSmsAdapter
    {
        return new JoypurHostSmsAdapter(
            $this->api_key,
            $this->sender_id,
            $this->url ?: JoypurHostSmsAdapter::DEFAULT_URL
        );
    }

    public function getBalance(): ?string
    {
        if (empty($this->api_key)) {
            return null;
        }

        return $this->toAdapter()->getBalance();
    }

    public function testConnection(): array
    {
        if (empty($this->api_key)) {
            return [
                'success' => false,
                'current_balance' => null,
                'message' => 'API Key is required to test JoypurHost SMS connection.',
            ];
        }

        return $this->toAdapter()->testConnection();
    }

    public function send(string $recipientPhone, string $message): bool
    {
        return $this->toAdapter()->send($recipientPhone, $message);
    }
}
