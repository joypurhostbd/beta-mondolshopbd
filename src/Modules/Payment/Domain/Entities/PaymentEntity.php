<?php

namespace Modules\Payment\Domain\Entities;

use Shared\Domain\Contracts\EntityInterface;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\ValueObjects\Money;

final class PaymentEntity implements EntityInterface
{
    public function __construct(
        private ?int $id,
        private int|string $orderId,
        private ?int $customerId,
        private PaymentMethodEnum $paymentMethod,
        private Money $amount,
        private PaymentStatusEnum $paymentStatus,
        private ?string $transactionId = null,
        private ?string $bankTransactionId = null,
        private array $gatewayResponse = [],
        private ?string $paidAt = null
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): int|string
    {
        return $this->orderId;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    public function getPaymentMethod(): PaymentMethodEnum
    {
        return $this->paymentMethod;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getPaymentStatus(): PaymentStatusEnum
    {
        return $this->paymentStatus;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getBankTransactionId(): ?string
    {
        return $this->bankTransactionId;
    }

    public function getGatewayResponse(): array
    {
        return $this->gatewayResponse;
    }

    public function getPaidAt(): ?string
    {
        return $this->paidAt;
    }

    public function markAsPaid(string $transactionId, ?string $bankTransactionId = null, array $raw = []): void
    {
        $this->paymentStatus = PaymentStatusEnum::PAID;
        $this->transactionId = $transactionId;
        $this->bankTransactionId = $bankTransactionId;
        $this->gatewayResponse = $raw;
        $this->paidAt = date('Y-m-d H:i:s');
    }

    public function markAsFailed(string $reason, array $raw = []): void
    {
        $this->paymentStatus = PaymentStatusEnum::FAILED;
        $this->gatewayResponse = array_merge($raw, ['error' => $reason]);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->orderId,
            'customer_id' => $this->customerId,
            'payment_method' => $this->paymentMethod->value,
            'amount' => $this->amount->getAmount(),
            'payment_status' => $this->paymentStatus->value,
            'transaction_id' => $this->transactionId,
            'bank_transaction_id' => $this->bankTransactionId,
            'gateway_response' => $this->gatewayResponse,
            'paid_at' => $this->paidAt,
        ];
    }
}