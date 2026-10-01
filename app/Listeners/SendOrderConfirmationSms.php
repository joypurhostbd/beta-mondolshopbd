<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SendSmsJob;
use App\Models\GeneralSetting;
use App\Models\SmsGateway;

class SendOrderConfirmationSms
{
    public function handle(OrderPlaced $event): void
    {
        $gateway = SmsGateway::getActive();

        if (!$gateway) {
            $gateway = SmsGateway::where('status', 1)->first();
        }

        // Only send if gateway is active and Order Confirmation toggle is enabled
        if (!$gateway || (int) $gateway->status !== 1 || (int) $gateway->order !== 1) {
            return;
        }

        $order = $event->order;
        $shipping = $order->shipping;
        $clientPhone = $shipping->phone ?? ($order->customer->phone ?? null);
        $customerName = $shipping->name ?? ($order->customer->name ?? 'Customer');

        // 1. Dispatch SMS to Client
        if (!empty($clientPhone)) {
            $siteSetting = GeneralSetting::where('status', 1)->first();
            $siteName = $siteSetting ? $siteSetting->name : config('app.name', 'MondolShopBD');
            $clientMessage = "Dear {$customerName}, your order #{$order->invoice_id} has been placed successfully! Total: {$order->amount} Tk. Thank you for shopping with {$siteName}.";
            SendSmsJob::dispatch($clientPhone, $clientMessage);
        }

        // 2. Dispatch SMS to all configured Admin Numbers
        $adminNumbers = $gateway->getAdminPhoneNumbers();
        if (!empty($adminNumbers)) {
            $adminMessage = "New Order Alert! Order #{$order->invoice_id} placed by {$customerName} (" . ($clientPhone ?: 'N/A') . "). Amount: {$order->amount} Tk.";
            foreach ($adminNumbers as $adminPhone) {
                SendSmsJob::dispatch($adminPhone, $adminMessage);
            }
        }
    }
}