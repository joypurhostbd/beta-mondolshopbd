<?php

namespace Modules\Order\Infrastructure\Repositories;

use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use Illuminate\Support\Facades\DB;
use Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use Modules\Order\Domain\Entities\OrderEntity;
use Modules\Order\Domain\Entities\OrderItemEntity;
use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;
use Shared\Domain\ValueObjects\Quantity;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function save(OrderEntity $order): OrderEntity
    {
        return DB::transaction(function () use ($order) {
            $orderModel = new Order();
            $orderModel->invoice_id = $order->getInvoiceId();
            $orderModel->amount = $order->getAmount()->getAmount();
            $orderModel->discount = $order->getDiscount()->getAmount();
            $orderModel->shipping_charge = $order->getShippingCharge()->getAmount();
            $orderModel->customer_id = $order->getCustomerId() ?? 0;
            $orderModel->order_status = $order->getOrderStatus()->value;
            $orderModel->note = $order->getNote();
            $orderModel->ip_address = $order->getIpAddress();
            $orderModel->save();

            // Shipping
            $shippingModel = new Shipping();
            $shippingModel->order_id = $orderModel->id;
            $shippingModel->customer_id = $order->getCustomerId() ?? 0;
            $shippingModel->name = $order->getCustomerName();
            $shippingModel->phone = $order->getPhoneNumber()->toE164();
            $shippingModel->address = $order->getShippingAddress();
            $shippingModel->area = $order->getArea() ?? 1;
            $shippingModel->save();

            // Payment
            $paymentModel = new Payment();
            $paymentModel->order_id = $orderModel->id;
            $paymentModel->customer_id = $order->getCustomerId() ?? 0;
            $paymentModel->payment_method = $order->getPaymentMethod()->value;
            $paymentModel->amount = $order->getAmount()->getAmount();
            $paymentModel->payment_status = $order->getPaymentStatus()->value;
            $paymentModel->save();

            // Line items
            $savedItems = [];
            foreach ($order->getItems() as $item) {
                $detail = new OrderDetails();
                $detail->order_id = $orderModel->id;
                $detail->product_id = (int) $item->getProductId();
                $detail->product_name = $item->getProductName();
                $detail->sale_price = $item->getUnitPrice()->getAmount();
                $detail->purchase_price = $item->getPurchasePrice()?->getAmount() ?? 0;
                $detail->qty = $item->getQuantity()->getValue();
                $detail->product_size = $item->getSize();
                $detail->product_color = $item->getColor();
                $detail->save();

                $savedItems[] = new OrderItemEntity(
                    $detail->id,
                    $item->getProductId(),
                    $item->getProductName(),
                    $item->getUnitPrice(),
                    $item->getQuantity(),
                    $item->getPurchasePrice(),
                    $item->getSize(),
                    $item->getColor()
                );
            }

            return new OrderEntity(
                $orderModel->id,
                $orderModel->invoice_id,
                $order->getCustomerId(),
                $order->getAmount(),
                $order->getDiscount(),
                $order->getShippingCharge(),
                $order->getOrderStatus(),
                $order->getCustomerName(),
                $order->getPhoneNumber(),
                $order->getShippingAddress(),
                $order->getArea(),
                $order->getPaymentMethod(),
                $order->getPaymentStatus(),
                $savedItems,
                $order->getNote(),
                $order->getIpAddress()
            );
        });
    }

    public function findById(int|string $id): ?OrderEntity
    {
        $orderModel = Order::with(['orderdetails', 'shipping', 'payment'])->find($id);
        if (!$orderModel) {
            return null;
        }

        return $this->toEntity($orderModel);
    }

    public function findByInvoice(string $invoiceId): ?OrderEntity
    {
        $orderModel = Order::with(['orderdetails', 'shipping', 'payment'])
            ->where('invoice_id', $invoiceId)
            ->first();

        if (!$orderModel) {
            return null;
        }

        return $this->toEntity($orderModel);
    }

    public function updateStatus(int|string $id, OrderStatusEnum $status): bool
    {
        return (bool) Order::where('id', $id)->update(['order_status' => $status->value]);
    }

    private function toEntity(Order $model): OrderEntity
    {
        $shipping = $model->shipping;
        $items = [];
        foreach ($model->orderdetails as $detail) {
            $items[] = new OrderItemEntity(
                $detail->id,
                $detail->product_id,
                $detail->product_name ?? '',
                Money::from((float) $detail->sale_price),
                Quantity::from((int) $detail->qty),
                Money::from((float) $detail->purchase_price),
                $detail->product_size,
                $detail->product_color
            );
        }

        return new OrderEntity(
            $model->id,
            $model->invoice_id ?? '',
            $model->customer_id ? (int) $model->customer_id : null,
            Money::from((float) $model->amount),
            Money::from((float) $model->discount),
            Money::from((float) $model->shipping_charge),
            OrderStatusEnum::tryFrom((int) $model->order_status) ?? OrderStatusEnum::PENDING,
            $shipping?->name ?? 'Guest Customer',
            PhoneNumber::fromString($shipping?->phone ?? '01700000000'),
            $shipping?->address ?? '',
            $shipping?->area,
            PaymentMethodEnum::COD,
            PaymentStatusEnum::PENDING,
            $items,
            $model->note,
            $model->ip_address
        );
    }
}