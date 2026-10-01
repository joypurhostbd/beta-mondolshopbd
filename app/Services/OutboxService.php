<?php

namespace App\Services;

use App\Models\OutboxMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Throwable;

class OutboxService
{
    /**
     * Record a new outbox domain message within a database transaction.
     *
     * @param string $eventName
     * @param array $payload
     * @return OutboxMessage
     */
    public function record(string $eventName, array $payload): OutboxMessage
    {
        return OutboxMessage::create([
            'event_name' => $eventName,
            'payload' => $payload,
            'status' => 'pending',
            'retry_count' => 0,
        ]);
    }

    /**
     * Process and publish pending outbox messages.
     *
     * @param int $limit
     * @return int Count of successfully published messages
     */
    public function publishPending(int $limit = 50): int
    {
        $messages = OutboxMessage::where('status', 'pending')
            ->where('retry_count', '<', 5)
            ->oldest()
            ->limit($limit)
            ->get();

        $publishedCount = 0;

        foreach ($messages as $message) {
            try {
                // Dispatch domain event dynamically
                Event::dispatch($message->event_name, [$message->payload]);

                $message->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'error_message' => null,
                ]);

                $publishedCount++;
            } catch (Throwable $e) {
                Log::error("Failed to publish outbox message [ID: {$message->id}]: " . $e->getMessage());

                $message->increment('retry_count', 1, [
                    'status' => ($message->retry_count + 1 >= 5) ? 'failed' : 'pending',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return $publishedCount;
    }
}