<?php

namespace Modules\Customer\Domain\Events;

use Shared\Domain\Contracts\DomainEventInterface;

class CustomerPasswordResetRequestedEvent implements DomainEventInterface
{
    private string $occurredAt;

    public function __construct(
        public readonly int|string $customerId,
        public readonly string $phone,
        public readonly int $otp
    ) {
        $this->occurredAt = date('Y-m-d H:i:s');
    }

    public function getEventName(): string
    {
        return 'customer.password_reset_requested';
    }

    public function getOccurredAt(): string
    {
        return $this->occurredAt;
    }

    public function toPayload(): array
    {
        return [
            'customer_id' => $this->customerId,
            'phone' => $this->phone,
            'otp' => $this->otp,
        ];
    }
}