<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Setting\Application\Services\ServerGoogleTagManagerService;

class SendServerGtmEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $eventName;
    public array $eventParams;
    public array $userData;
    public ?string $eventId;
    public ?int $orderId;

    public int $tries = 2;
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $eventName,
        array $eventParams = [],
        array $userData = [],
        ?string $eventId = null,
        ?int $orderId = null
    ) {
        $this->eventName = $eventName;
        $this->eventParams = $eventParams;
        $this->userData = $userData;
        $this->eventId = $eventId;
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(ServerGoogleTagManagerService $gtmService): void
    {
        try {
            if ($this->eventName === 'purchase' && $this->orderId) {
                $order = Order::find($this->orderId);
                if ($order) {
                    $res = $gtmService->trackPurchase($order, $this->eventId, $this->userData);
                    Log::info("Server-Side GTM [purchase] dispatched for order #{$this->orderId}", [
                        'event_id' => $this->eventId,
                        'dispatched_count' => $res['dispatched_count'] ?? 0,
                    ]);
                    return;
                }
            }

            $res = $gtmService->sendEvent(
                $this->eventName,
                $this->eventParams,
                $this->userData,
                $this->eventId
            );

            Log::info("Server-Side GTM [{$this->eventName}] dispatched", [
                'event_id' => $this->eventId,
                'dispatched_count' => $res['dispatched_count'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            Log::error("SendServerGtmEventJob failed for [{$this->eventName}]: " . $e->getMessage(), [
                'event_id' => $this->eventId,
                'order_id' => $this->orderId,
            ]);
        }
    }
}
