<?php

namespace Modules\Order\Application\DTOs;

use Shared\Application\DTO\DataTransferObject;
use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;

final class AdminPosOrderInputDTO extends DataTransferObject
{
    /**
     * @param array<int, array{productId: int|string, productName: string, unitPrice: float, purchasePrice?: float, quantity: int, size?: ?string, color?: ?string}> $items
     */
    public function __construct(
        public readonly array $items,
        public readonly string $customerName,
        public readonly string $phone,
        public readonly string $address,
        public readonly string|int $area = 1,
        public readonly float $shippingCost = 0.0,
        public readonly float $discount = 0.0,
        public readonly ?string $note = null,
        public readonly ?string $ipAddress = '127.0.0.1',
        public readonly PaymentMethodEnum $paymentMethod = PaymentMethodEnum::COD,
        public readonly PaymentStatusEnum $paymentStatus = PaymentStatusEnum::PENDING,
        public readonly OrderStatusEnum $orderStatus = OrderStatusEnum::PENDING,
        public readonly ?int $adminUserId = null
    ) {}

    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'customer_name' => $this->customerName,
            'phone' => $this->phone,
            'address' => $this->address,
            'area' => $this->area,
            'shipping_cost' => $this->shippingCost,
            'discount' => $this->discount,
            'note' => $this->note,
            'ip_address' => $this->ipAddress,
            'payment_method' => $this->paymentMethod->value,
            'payment_status' => $this->paymentStatus->value,
            'order_status' => $this->orderStatus->value,
            'admin_user_id' => $this->adminUserId,
        ];
    }
}