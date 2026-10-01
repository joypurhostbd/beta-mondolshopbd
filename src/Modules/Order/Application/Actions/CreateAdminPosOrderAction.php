<?php

namespace Modules\Order\Application\Actions;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Modules\Order\Application\DTOs\AdminPosOrderInputDTO;
use Modules\Order\Application\DTOs\OrderDTO;
use Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use Modules\Order\Domain\Entities\OrderEntity;
use Modules\Order\Domain\Entities\OrderItemEntity;
use Modules\Order\Domain\Events\OrderPlacedEvent;
use Modules\Order\Domain\Services\PricingEngine;
use Shared\Domain\Contracts\Modules\InventoryModuleInterface;
use Shared\Domain\Exceptions\DomainException;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;
use Shared\Domain\ValueObjects\Quantity;

class CreateAdminPosOrderAction
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private PricingEngine $pricingEngine,
        private InventoryModuleInterface $inventoryService
    ) {}

    public function execute(AdminPosOrderInputDTO $input): OrderDTO
    {
        if (empty($input->items)) {
            throw new DomainException("POS order must contain at least one item.");
        }

        return DB::transaction(function () use ($input) {
            // 1. Resolve or create customer
            $phoneVO = PhoneNumber::fromString($input->phone);
            $customer = Customer::where('phone', $phoneVO->getValue())
                ->orWhere('phone', $phoneVO->getInternational())
                ->first();

            if (!$customer) {
                $customer = new Customer();
                $customer->name = $input->customerName;
                $customer->slug = Str::slug($input->customerName) . '-' . rand(100, 999);
                $customer->phone = $phoneVO->getValue();
                $customer->password = bcrypt(Str::random(12));
                $customer->verify = 1;
                $customer->status = 'active';
                $customer->save();
            }

            // 2. Calculate Subtotal and Reserve Inventory
            $subtotalAmount = 0.0;
            $orderItems = [];

            foreach ($input->items as $itemData) {
                $productId = $itemData['productId'];
                $qty = (int) ($itemData['quantity'] ?? 1);
                $unitPrice = (float) $itemData['unitPrice'];
                $productName = $itemData['productName'] ?? 'Product';

                // Reserve stock atomically
                $this->inventoryService->reserveStock($productId, $qty);

                // Fetch product purchase price if not given
                $purchasePrice = isset($itemData['purchasePrice'])
                    ? Money::from((float) $itemData['purchasePrice'])
                    : Money::zero();

                if ($purchasePrice->isZero()) {
                    $product = Product::find($productId);
                    if ($product) {
                        $purchasePrice = Money::from((float) ($product->purchase_price ?? 0));
                    }
                }

                $orderItems[] = new OrderItemEntity(
                    id: null,
                    productId: $productId,
                    productName: $productName,
                    unitPrice: Money::from($unitPrice),
                    quantity: Quantity::from($qty),
                    purchasePrice: $purchasePrice,
                    size: $itemData['size'] ?? null,
                    color: $itemData['color'] ?? null
                );

                $subtotalAmount += ($unitPrice * $qty);
            }

            // 3. Compute Price Breakdown
            $discountVO = $input->discount > 0
                ? \Shared\Domain\ValueObjects\Discount::fixed($input->discount)
                : null;

            $priceBreakdown = $this->pricingEngine->calculateFromAmounts(
                subtotal: Money::from($subtotalAmount),
                discount: $discountVO,
                shippingCharge: Money::from($input->shippingCost),
                itemCount: count($orderItems),
                totalQuantity: count($orderItems)
            );

            // 4. Generate Invoice ID
            $invoiceId = 'POS-' . strtoupper(substr(uniqid(), -6));

            $orderEntity = new OrderEntity(
                id: null,
                invoiceId: $invoiceId,
                customerId: $customer->id,
                amount: Money::from($priceBreakdown->grandTotal),
                discount: Money::from($priceBreakdown->discountAmount),
                shippingCharge: Money::from($priceBreakdown->shippingCharge),
                orderStatus: $input->orderStatus,
                customerName: $input->customerName,
                phoneNumber: $phoneVO,
                shippingAddress: $input->address,
                area: (string) $input->area,
                paymentMethod: $input->paymentMethod,
                paymentStatus: $input->paymentStatus,
                items: $orderItems,
                note: $input->note,
                ipAddress: $input->ipAddress
            );

            // 5. Save order to database
            $savedOrder = $this->orderRepository->save($orderEntity);

            // 6. Dispatch domain event
            Event::dispatch(new OrderPlacedEvent(
                orderId: $savedOrder->getId(),
                invoiceId: $savedOrder->getInvoiceId(),
                customerId: $savedOrder->getCustomerId(),
                customerName: $savedOrder->getCustomerName(),
                phoneNumber: $savedOrder->getPhoneNumber(),
                amount: $savedOrder->getAmount(),
                itemsCount: count($savedOrder->getItems())
            ));

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