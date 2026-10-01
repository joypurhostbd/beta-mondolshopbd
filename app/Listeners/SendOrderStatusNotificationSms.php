<?php

namespace App\Listeners;

use App\Jobs\SendSmsJob;
use App\Models\Order;
use Modules\Order\Domain\Events\OrderStatusChangedEvent;
use Shared\Domain\Enums\OrderStatusEnum;

class SendOrderStatusNotificationSms
{
    public function handle(OrderStatusChangedEvent $event): void
    {
        $order = Order::with('shipping')->find($event->orderId);

        if (!$order || !$order->shipping || empty($order->shipping->phone)) {
            return;
        }

        $phone = $order->shipping->phone;
        $name = $order->shipping->name ?? 'Customer';
        $invoiceId = $order->invoice_id;
        $message = null;

        switch ($event->newStatus) {
            case OrderStatusEnum::PROCESSING:
            case OrderStatusEnum::COMPLETED:
                $consignmentText = $order->consignment_id ? " Consignment: {$order->consignment_id}." : "";
                $trackUrl = url('/order-track/result?invoice_id=' . $order->invoice_id . '&phone=' . $phone);
                $message = "Dear {$name}, your order #{$invoiceId} is on the way!{$consignmentText} Track: {$trackUrl}";
                break;

            case OrderStatusEnum::DELIVERED:
                $message = "Dear {$name}, your order #{$invoiceId} has been successfully delivered. Thank you for shopping with us!";
                break;

            case OrderStatusEnum::CANCELLED:
                $message = "Dear {$name}, your order #{$invoiceId} has been cancelled. Contact support for any queries.";
                break;

            default:
                break;
        }

        if ($message) {
            SendSmsJob::dispatch($phone, $message);
        }
    }
}
