<?php

namespace Modules\Order\Domain\Events;

use Shared\Domain\Contracts\DomainEventInterface;
use Shared\Domain\Enums\OrderStatusEnum;

class OrderStatusChangedEvent implements DomainEventInterface
{
    private string $occurredAt;

    public function __construct(
        public readonly int $orderId,
        public readonly string $invoiceId,
        public readonly OrderStatusEnum $oldStatus,
        public readonly OrderStatusEnum $newStatus,
        public readonly ?string $reason = null,
        public readonly ?string $updatedBy = null
    ) {
        $this->occurredAt = date('Y-m-d H:i:s');
    }

    public function getEventName(): string
    {
        return 'order.status_changed';
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
            'old_status' => $this->oldStatus->value,
            'old_status_label' => $this->oldStatus->label(),
            'new_status' => $this->newStatus->value,
            'new_status_label' => $this->newStatus->label(),
            'reason' => $this->reason,
            'updated_by' => $this->updatedBy,
            'occurred_at' => $this->occurredAt,
        ];
    }
}