<?php

namespace Shared\Infrastructure\Services;

use App\Models\IdempotencyKey;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class IdempotencyService
{
    /**
     * Execute a callable idempotently based on key, path, and request payload hash.
     *
     * @param string $key
     * @param string $path
     * @param array|string $payload
     * @param callable $callback
     * @param int $ttlSeconds
     * @return array ['status_code' => int, 'body' => mixed, 'cached' => bool]
     */
    public function execute(string $key, string $path, array|string $payload, callable $callback, int $ttlSeconds = 86400): array
    {
        $payloadString = is_array($payload) ? json_encode($payload) : (string) $payload;
        $requestHash = hash('sha256', $payloadString);

        return DB::transaction(function () use ($key, $path, $requestHash, $callback, $ttlSeconds) {
            $existing = IdempotencyKey::where('idempotency_key', $key)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                // If expired, remove and re-evaluate
                if ($existing->expires_at && Carbon::parse($existing->expires_at)->isPast()) {
                    $existing->delete();
                } else if ($existing->response_body !== null) {
                    return [
                        'status_code' => $existing->status_code ?? 200,
                        'body' => json_decode($existing->response_body, true) ?? $existing->response_body,
                        'cached' => true,
                    ];
                }
            }

            // Create placeholder or initial record
            $record = IdempotencyKey::create([
                'idempotency_key' => $key,
                'request_path' => $path,
                'request_hash' => $requestHash,
                'status_code' => 200,
                'expires_at' => Carbon::now()->addSeconds($ttlSeconds),
            ]);

            try {
                $result = $callback();
                $record->response_body = json_encode($result);
                $record->status_code = 200;
                $record->save();

                return [
                    'status_code' => 200,
                    'body' => $result,
                    'cached' => false,
                ];
            } catch (Throwable $e) {
                // Remove failed lock record so retries can execute
                $record->delete();
                throw $e;
            }
        });
    }

    public function isProcessed(string $key): bool
    {
        $existing = IdempotencyKey::where('idempotency_key', $key)->first();
        return $existing !== null && $existing->response_body !== null && (!$existing->expires_at || Carbon::parse($existing->expires_at)->isFuture());
    }
}