<?php

namespace Modules\Order\Domain\Entities;

use Shared\Domain\Contracts\AggregateRootInterface;
use Shared\Domain\Contracts\DomainEventInterface;
use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;

class OrderEntity implements AggregateRootInterface
{
    /** @var DomainEventInterface[] */
    private array $recordedEvents = [];

    /**
     * @param OrderItemEntity[] $items
     */
    public function __construct(
        private ?int $id,
        private string $invoiceId,
        private ?int $customerId,
        private Money $amount,
        private Money $discount,
        private Money $shippingCharge,
        private OrderStatusEnum $orderStatus,
        private string $customerName,
        private PhoneNumber $phoneNumber,
        private string $shippingAddress,
        private string|int|null $area,
        private PaymentMethodEnum $paymentMethod,
        private PaymentStatusEnum $paymentStatus,
        private array $items = [],
        private ?string $note = null,
        private ?string $ipAddress = null
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceId(): string
    {
        return $this->invoiceId;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getDiscount(): Money
    {
        return $this->discount;
    }

    public function getShippingCharge(): Money
    {
        return $this->shippingCharge;
    }

    public function getOrderStatus(): OrderStatusEnum
    {
        return $this->orderStatus;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function getShippingAddress(): string
    {
        return $this->shippingAddress;
    }

    public function getArea(): string|int|null
    {
        return $this->area;
    }

    public function getPaymentMethod(): PaymentMethodEnum
    {
        return $this->paymentMethod;
    }

    public function getPaymentStatus(): PaymentStatusEnum
    {
        return $this->paymentStatus;
    }

    /**
     * @return OrderItemEntity[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function recordEvent(DomainEventInterface $event): void
    {
        $this->recordedEvents[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoiceId,
            'customer_id' => $this->customerId,
            'amount' => $this->amount->getAmount(),
            'discount' => $this->discount->getAmount(),
            'shipping_charge' => $this->shippingCharge->getAmount(),
            'order_status' => $this->orderStatus->value,
            'customer_name' => $this->customerName,
            'phone' => $this->phoneNumber->toE164(),
            'address' => $this->shippingAddress,
            'area' => $this->area,
            'payment_method' => $this->paymentMethod->value,
            'payment_status' => $this->paymentStatus->value,
            'items' => array_map(fn(OrderItemEntity $item) => $item->toArray(), $this->items),
            'note' => $this->note,
            'ip_address' => $this->ipAddress,
        ];
    }
}