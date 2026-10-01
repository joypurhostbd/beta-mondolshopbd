<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Courierapi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendToCourierJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    public function __construct(
        public int $orderId,
        public string $courierType,
    ) {}

    public function handle(): void
    {
        $order = Order::with('shipping', 'orderdetails')->find($this->orderId);

        if (!$order) {
            return;
        }

        Log::info("Courier shipment queued for order #{$order->invoice_id} via {$this->courierType}");
    }
}