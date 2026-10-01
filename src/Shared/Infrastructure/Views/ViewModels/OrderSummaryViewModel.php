<?php

namespace Shared\Infrastructure\Views\ViewModels;

use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\ValueObjects\Money;

class OrderSummaryViewModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $invoiceId,
        public readonly string $customerName,
        public readonly string $customerPhone,
        public readonly OrderStatusEnum $status,
        public readonly Money $subtotal,
        public readonly Money $shippingCharge,
        public readonly Money $discount,
        public readonly Money $totalAmount,
        public readonly array $items = []
    ) {}

    public function getStatusLabel(): string
    {
        return $this->status->label();
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            OrderStatusEnum::PENDING => 'badge-warning bg-yellow-100 text-yellow-800',
            OrderStatusEnum::PROCESSING => 'badge-info bg-blue-100 text-blue-800',
            OrderStatusEnum::ON_HOLD => 'badge-secondary bg-gray-100 text-gray-800',
            OrderStatusEnum::COMPLETED, OrderStatusEnum::DELIVERED => 'badge-success bg-green-100 text-green-800',
            OrderStatusEnum::CANCELLED => 'badge-danger bg-red-100 text-red-800',
            OrderStatusEnum::RETURNED => 'badge-dark bg-purple-100 text-purple-800',
        };
    }

    public function getFormattedSubtotal(): string
    {
        return $this->subtotal->format();
    }

    public function getFormattedShipping(): string
    {
        return $this->shippingCharge->format();
    }

    public function getFormattedDiscount(): string
    {
        return $this->discount->format();
    }

    public function getFormattedTotal(): string
    {
        return $this->totalAmount->format();
    }
}