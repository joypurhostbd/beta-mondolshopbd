<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SendServerGtmEventJob;
use Illuminate\Support\Facades\Log;

class SendServerGtmPurchaseConversion
{
    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        try {
            $order = $event->order;
            if (!$order) {
                return;
            }

            $eventId = 'order_' . ($order->invoice_id ?? $order->id);

            $order->loadMissing(['shipping', 'customer', 'orderdetails']);

            $userData = [
                'client_ip_address' => request()->ip(),
                'client_user_agent' => request()->userAgent(),
                'phone' => $order->shipping?->phone ?? $order->customer?->phone,
                'email' => $order->shipping?->email ?? $order->customer?->email,
                'first_name' => $order->shipping?->name ?? $order->customer?->name,
                'city' => $order->shipping?->area,
                'address' => $order->shipping?->address,
                'client_id' => request()->cookie('_ga'),
            ];

            SendServerGtmEventJob::dispatch(
                'purchase',
                [],
                $userData,
                $eventId,
                $order->id
            );
        } catch (\Throwable $e) {
            Log::error("SendServerGtmPurchaseConversion Listener Error: " . $e->getMessage());
        }
    }
}
