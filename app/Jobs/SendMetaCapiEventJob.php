<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\MetaConversionsApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMetaCapiEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $eventName;
    public array $userData;
    public array $customData;
    public ?string $eventId;
    public ?string $eventSourceUrl;
    public ?int $orderId;

    public int $tries = 2;
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $eventName,
        array $userData = [],
        array $customData = [],
        ?string $eventId = null,
        ?string $eventSourceUrl = null,
        ?int $orderId = null
    ) {
        $this->eventName = $eventName;
        $this->userData = $userData;
        $this->customData = $customData;
        $this->eventId = $eventId;
        $this->eventSourceUrl = $eventSourceUrl;
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(MetaConversionsApiService $capiService): void
    {
        try {
            if ($this->eventName === 'Purchase' && $this->orderId) {
                $order = Order::find($this->orderId);
                if ($order) {
                    $res = $capiService->trackPurchase($order, $this->eventId, $this->eventSourceUrl, $this->userData);
                    Log::info("Meta CAPI [Purchase] dispatched for order #{$this->orderId}", [
                        'event_id' => $this->eventId,
                        'success' => $res['success'] ?? false,
                    ]);
                    return;
                }
            }

            $res = $capiService->sendEvent(
                $this->eventName,
                $this->userData,
                $this->customData,
                $this->eventId,
                $this->eventSourceUrl
            );

            Log::info("Meta CAPI [{$this->eventName}] dispatched", [
                'event_id' => $this->eventId,
                'success' => $res['success'] ?? false,
            ]);
        } catch (\Throwable $e) {
            Log::error("SendMetaCapiEventJob Failed for [{$this->eventName}]: " . $e->getMessage(), [
                'event_id' => $this->eventId,
                'order_id' => $this->orderId,
            ]);
        }
    }
}