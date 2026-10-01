<?php

namespace App\Jobs;

use App\Models\SmsGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Domain\Contracts\SmsServiceInterface;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;
    public int $backoff = 10;

    public function __construct(
        public string $phone,
        public string $message,
    ) {}

    public function handle(SmsServiceInterface $smsService): void
    {
        $gateway = SmsGateway::where('status', 1)->first();

        if (!$gateway || (int) $gateway->status !== 1) {
            Log::info("SMS dispatch to {$this->phone}: {$this->message}");
            return;
        }

        try {
            $sent = $smsService->send($this->phone, $this->message);
            if (!$sent) {
                Log::warning("Failed to send SMS to {$this->phone}");
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to send SMS to {$this->phone}: " . $e->getMessage());
        }
    }
}