<?php

namespace Modules\Order\Application\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Order\Application\DTOs\OrderDTO;
use Modules\Order\Application\DTOs\PlaceOrderInputDTO;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;
use Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use Modules\Order\Domain\Entities\OrderEntity;
use Modules\Order\Domain\Entities\OrderItemEntity;
use Modules\Order\Domain\Events\OrderPlacedEvent;
use Modules\Order\Domain\Services\PricingEngine;
use Shared\Domain\Contracts\Modules\InventoryModuleInterface;
use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\Exceptions\DomainException;
use Shared\Domain\ValueObjects\Discount;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;
use Shared\Domain\ValueObjects\Quantity;

class PlaceOrderAction
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private OrderRepositoryInterface $orderRepository,
        private PricingEngine $pricingEngine,
        private InventoryModuleInterface $inventoryService
    ) {}

    public function execute(PlaceOrderInputDTO $input): OrderDTO
    {
        $cart = $this->cartRepository->get($input->cartKey);

        if ($cart->isEmpty()) {
            throw new DomainException('Cannot place an order with an empty cart.');
        }

        // 1. Calculate pricing
        $discount = null;
        if ($input->discountType === 'percentage' && $input->discountValue !== null && $input->discountValue > 0) {
            $discount = Discount::fromPercentage($input->discountValue);
        } elseif ($input->discountType === 'fixed' && $input->discountValue !== null && $input->discountValue > 0) {
            $discount = Discount::fromFixed(Money::from($input->discountValue));
        }

        $shipping = $input->shippingCost !== null && $input->shippingCost >= 0
            ? Money::from($input->shippingCost)
            : Money::zero();

        $priceBreakdown = $this->pricingEngine->calculate($cart, $discount, $shipping);

        // 2. Reserve stock & prepare order items within transaction
        return DB::transaction(function () use ($input, $cart, $priceBreakdown) {
            $orderItems = [];
            foreach ($cart->getItems() as $item) {
                // Reserve stock atomically via inventory module
                $this->inventoryService->reserveStock($item->getProductId(), $item->getQuantity()->getValue());

                // Lookup purchase price if available
                $product = Product::find($item->getProductId());
                $purchasePrice = $product ? Money::from((float) ($product->purchase_price ?? 0)) : Money::zero();

                $orderItems[] = new OrderItemEntity(
                    id: null,
                    productId: $item->getProductId(),
                    productName: $item->getProductName(),
                    unitPrice: $item->getPrice(),
                    quantity: $item->getQuantity(),
                    purchasePrice: $purchasePrice,
                    size: $item->getSize(),
                    color: $item->getColor()
                );
            }

            // 3. Generate unique invoice ID
            $invoiceId = 'INV-' . strtoupper(substr(uniqid(), -6));

            $orderEntity = new OrderEntity(
                id: null,
                invoiceId: $invoiceId,
                customerId: $input->customerId,
                amount: Money::from($priceBreakdown->grandTotal),
                discount: Money::from($priceBreakdown->discountAmount),
                shippingCharge: Money::from($priceBreakdown->shippingCharge),
                orderStatus: OrderStatusEnum::PENDING,
                customerName: $input->customerName,
                phoneNumber: PhoneNumber::fromString($input->phone),
                shippingAddress: $input->address,
                area: $input->area,
                paymentMethod: PaymentMethodEnum::COD,
                paymentStatus: PaymentStatusEnum::PENDING,
                items: $orderItems,
                note: $input->note,
                ipAddress: $input->ipAddress
            );

            // 4. Save order to database
            $savedOrder = $this->orderRepository->save($orderEntity);

            // 5. Clear cart
            $this->cartRepository->delete($input->cartKey);

            // 6. Dispatch domain event
            $event = new OrderPlacedEvent(
                orderId: $savedOrder->getId(),
                invoiceId: $savedOrder->getInvoiceId(),
                customerId: $savedOrder->getCustomerId(),
                customerName: $savedOrder->getCustomerName(),
                phoneNumber: $savedOrder->getPhoneNumber(),
                amount: $savedOrder->getAmount(),
                itemsCount: count($savedOrder->getItems())
            );
            Event::dispatch($event);

            return new OrderDTO(
                id: $savedOrder->getId(),
                invoiceId: $savedOrder->getInvoiceId(),
                customerId: $savedOrder->getCustomerId(),
                amount: $savedOrder->getAmount()->getAmount(),
                amountFormatted: $savedOrder->getAmount()->format(),
                discount: $savedOrder->getDiscount()->getAmount(),
                shippingCharge: $savedOrder->getShippingCharge()->getAmount(),
                orderStatus: $savedOrder->getOrderStatus()->label(),
                customerName: $savedOrder->getCustomerName(),
                phone: $savedOrder->getPhoneNumber()->toE164(),
                address: $savedOrder->getShippingAddress(),
                items: array_map(fn(OrderItemEntity $i) => $i->toArray(), $savedOrder->getItems())
            );
        });
    }
}