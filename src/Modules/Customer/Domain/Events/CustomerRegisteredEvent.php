<?php

namespace Modules\Customer\Domain\Events;

use Shared\Domain\Contracts\DomainEventInterface;

class CustomerRegisteredEvent implements DomainEventInterface
{
    private string $occurredAt;

    public function __construct(
        public readonly int|string $customerId,
        public readonly string $name,
        public readonly string $phone
    ) {
        $this->occurredAt = date('Y-m-d H:i:s');
    }

    public function getEventName(): string
    {
        return 'customer.registered';
    }

    public function getOccurredAt(): string
    {
        return $this->occurredAt;
    }

    public function toPayload(): array
    {
        return [
            'customer_id' => $this->customerId,
            'name' => $this->name,
            'phone' => $this->phone,
        ];
    }
}