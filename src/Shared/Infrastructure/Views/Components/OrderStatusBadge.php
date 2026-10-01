<?php

namespace Shared\Infrastructure\Views\Components;

use Illuminate\View\Component;
use Shared\Domain\Enums\OrderStatusEnum;

class OrderStatusBadge extends Component
{
    public function __construct(
        public OrderStatusEnum|int|string $status
    ) {}

    public function enum(): OrderStatusEnum
    {
        if ($this->status instanceof OrderStatusEnum) {
            return $this->status;
        }
        return OrderStatusEnum::tryFrom((int) $this->status) ?? OrderStatusEnum::PENDING;
    }

    public function label(): string
    {
        return $this->enum()->label();
    }

    public function badgeClass(): string
    {
        return match ($this->enum()) {
            OrderStatusEnum::PENDING => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            OrderStatusEnum::PROCESSING => 'bg-blue-100 text-blue-800 border-blue-300',
            OrderStatusEnum::ON_HOLD => 'bg-gray-100 text-gray-800 border-gray-300',
            OrderStatusEnum::COMPLETED, OrderStatusEnum::DELIVERED => 'bg-green-100 text-green-800 border-green-300',
            OrderStatusEnum::CANCELLED => 'bg-red-100 text-red-800 border-red-300',
            OrderStatusEnum::RETURNED => 'bg-purple-100 text-purple-800 border-purple-300',
        };
    }

    public function render()
    {
        return view('components.shared.order-status-badge');
    }
}