<?php

namespace Modules\Order\Domain\Events;

use Shared\Domain\Contracts\DomainEventInterface;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;

class OrderPlacedEvent implements DomainEventInterface
{
    private string $occurredAt;

    public function __construct(
        public readonly int $orderId,
        public readonly string $invoiceId,
        public readonly ?int $customerId,
        public readonly string $customerName,
        public readonly PhoneNumber $phoneNumber,
        public readonly Money $amount,
        public readonly int $itemsCount
    ) {
        $this->occurredAt = date('Y-m-d H:i:s');
    }

    public function getEventName(): string
    {
        return 'order.placed';
    }

    public function getOccurredAt(): string
    {
        return $this->occurredAt;
    }

    public function toPayload(): array
    {
        return [
            'order_id' => $this->orderId,
            'invoice_id' => $this->invoiceId,
            'customer_id' => $this->customerId,
            'customer_name' => $this->customerName,
            'phone' => $this->phoneNumber->toE164(),
            'amount' => $this->amount->getAmount(),
            'items_count' => $this->itemsCount,
            'occurred_at' => $this->occurredAt,
        ];
    }
}