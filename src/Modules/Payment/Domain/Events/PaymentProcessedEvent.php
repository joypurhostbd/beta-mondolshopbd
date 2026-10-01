<?php

namespace Modules\Payment\Domain\Events;

use Shared\Domain\Contracts\DomainEventInterface;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\ValueObjects\Money;

class PaymentProcessedEvent implements DomainEventInterface
{
    private string $occurredAt;

    public function __construct(
        public readonly int|string $orderId,
        public readonly PaymentMethodEnum $method,
        public readonly Money $amount,
        public readonly PaymentStatusEnum $status,
        public readonly ?string $transactionId = null,
        public readonly ?string $errorMessage = null
    ) {
        $this->occurredAt = date('Y-m-d H:i:s');
    }

    public function getEventName(): string
    {
        return 'payment.processed';
    }

    public function getOccurredAt(): string
    {
        return $this->occurredAt;
    }

    public function toPayload(): array
    {
        return [
            'order_id' => $this->orderId,
            'method' => $this->method->value,
            'amount' => $this->amount->getAmount(),
            'status' => $this->status->value,
            'transaction_id' => $this->transactionId,
            'error_message' => $this->errorMessage,
            'occurred_at' => $this->occurredAt,
        ];
    }
}