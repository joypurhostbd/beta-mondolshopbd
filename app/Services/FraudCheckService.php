<?php

namespace App\Services;

use App\Models\Courierapi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FraudCheckService
{
    /**
     * Cache TTL in seconds (6 hours).
     */
    protected int $cacheTtl = 21600;

    /**
     * Default Hoorin Fraud Check API Endpoint.
     */
    public const DEFAULT_URL = 'https://dash.hoorin.com/api/courier/api';

    /**
     * Resolve the target API endpoint URL.
     *
     * @param string|null $customUrl
     * @return string
     */
    public function getEndpointUrl(?string $customUrl = null): string
    {
        if (!empty($customUrl)) {
            return trim($customUrl);
        }

        $savedUrl = Courierapi::where('type', 'fraud')->value('url');

        return !empty($savedUrl) ? trim($savedUrl) : self::DEFAULT_URL;
    }

    /**
     * Build standard HTTP client configured with IPv4 resolve, headers, and timeouts.
     *
     * @param int $timeoutSeconds
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function buildHttpClient(int $timeoutSeconds = 15): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withOptions([
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
        ])
        ->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'application/json',
        ])
        ->timeout($timeoutSeconds)
        ->retry(2, 200, throw: false);
    }

    /**
     * Check courier delivery history & fraud indicators for a phone number.
     *
     * @param string $phone
     * @param bool $forceRefresh
     * @return array
     */
    public function check(string $phone, bool $forceRefresh = false): array
    {
        $cleanPhone = $this->cleanPhone($phone);

        if (empty($cleanPhone)) {
            return [
                'error' => 'No phone number provided',
                'total_stats' => null,
                'courier_breakdown' => $this->parseCourierBreakdown(null),
                'individual_response' => null,
                'status' => 'error'
            ];
        }

        $fraud = Courierapi::where('type', 'fraud')->first();

        if (!$fraud || empty($fraud->token)) {
            return [
                'error' => 'API Token not Found',
                'total_stats' => null,
                'courier_breakdown' => $this->parseCourierBreakdown(null),
                'individual_response' => null,
                'status' => 'error'
            ];
        }

        if ((int) $fraud->status !== 1) {
            return [
                'error' => 'Fraud check is currently disabled',
                'total_stats' => null,
                'courier_breakdown' => $this->parseCourierBreakdown(null),
                'individual_response' => null,
                'status' => 'disabled'
            ];
        }

        $cacheKey = "fraud_check_{$cleanPhone}";

        if (!$forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $endpoint = $this->getEndpointUrl($fraud->url ?? null);
            $response = $this->buildHttpClient(15)->get($endpoint, [
                'apiKey' => $fraud->token,
                'searchTerm' => $cleanPhone,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $stats = $this->calculateStats($data);
                $breakdown = $this->parseCourierBreakdown($data);

                $result = [
                    'total_stats' => $stats,
                    'courier_breakdown' => $breakdown,
                    'individual_response' => $data,
                    'status' => 'success',
                ];

                Cache::put($cacheKey, $result, now()->addSeconds($this->cacheTtl));

                return $result;
            }
        } catch (\Throwable $e) {
            Log::warning("Fraud check API error: " . $e->getMessage());
        }

        return [
            'error' => 'API request failed',
            'total_stats' => null,
            'courier_breakdown' => $this->parseCourierBreakdown(null),
            'individual_response' => null,
            'status' => 'error'
        ];
    }

    /**
     * Get cached fraud check result if available, without triggering external API calls.
     *
     * @param string $phone
     * @return array|null
     */
    public function getCached(string $phone): ?array
    {
        $cleanPhone = $this->cleanPhone($phone);
        if (empty($cleanPhone)) {
            return null;
        }

        $cacheKey = "fraud_check_{$cleanPhone}";

        return Cache::get($cacheKey);
    }

    /**
     * Clean phone number to digits only.
     *
     * @param string $phone
     * @return string
     */
    public function cleanPhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone) ?? '';
    }

    /**
     * Parse courier-specific delivery and return breakdown statistics.
     *
     * @param array|null $data
     * @return array<string, array{orders: int, delivered: int, canceled: int, return_rate: int, success_rate: int}>
     */
    public function parseCourierBreakdown(?array $data): array
    {
        $couriers = [
            'Steadfast' => ['orders' => 0, 'delivered' => 0, 'canceled' => 0, 'return_rate' => 0, 'success_rate' => 0],
            'RedX' => ['orders' => 0, 'delivered' => 0, 'canceled' => 0, 'return_rate' => 0, 'success_rate' => 0],
            'Pathao' => ['orders' => 0, 'delivered' => 0, 'canceled' => 0, 'return_rate' => 0, 'success_rate' => 0],
            'Carrybee' => ['orders' => 0, 'delivered' => 0, 'canceled' => 0, 'return_rate' => 0, 'success_rate' => 0],
        ];

        $items = $data['Summaries'] ?? (is_array($data) ? $data : []);

        if (is_array($items)) {
            foreach ($items as $courierName => $item) {
                if (!is_array($item)) {
                    continue;
                }

                $total = (int) ($item['Total Parcels'] ?? $item['Total Delivery'] ?? $item['total_parcel'] ?? $item['total'] ?? 0);
                $delivered = (int) ($item['Delivered Parcels'] ?? $item['Successful Delivery'] ?? $item['success_parcel'] ?? $item['delivered_parcel'] ?? $item['delivered'] ?? 0);
                $canceled = (int) ($item['Canceled Parcels'] ?? $item['Cancelled Parcels'] ?? $item['Canceled Delivery'] ?? $item['Cancelled Delivery'] ?? $item['cancelled_parcel'] ?? $item['canceled_parcel'] ?? $item['cancelled'] ?? 0);

                $returnRate = $total > 0 ? (int) round(($canceled / $total) * 100) : 0;
                $successRate = $total > 0 ? (int) round(($delivered / $total) * 100) : 0;

                // Match known couriers case-insensitively or register dynamically
                $targetKey = ucfirst((string) $courierName);
                foreach (array_keys($couriers) as $knownKey) {
                    if (strcasecmp($knownKey, (string) $courierName) === 0) {
                        $targetKey = $knownKey;
                        break;
                    }
                }

                $couriers[$targetKey] = [
                    'orders' => $total,
                    'delivered' => $delivered,
                    'canceled' => $canceled,
                    'return_rate' => $returnRate,
                    'success_rate' => $successRate,
                ];
            }
        }

        return $couriers;
    }

    /**
     * Calculate aggregated parcel and delivery statistics from API response.
     *
     * @param array|null $data
     * @return array
     */
    public function calculateStats(?array $data): array
    {
        $total_parcel = 0;
        $total_delivered = 0;
        $total_cancel = 0;

        $items = $data['Summaries'] ?? (is_array($data) ? $data : []);

        if (is_array($items)) {
            foreach ($items as $courier => $item) {
                if (!is_array($item)) {
                    continue;
                }

                $total = (int) ($item['Total Parcels'] ?? $item['Total Delivery'] ?? $item['total_parcel'] ?? $item['total'] ?? 0);
                $delivered = (int) ($item['Delivered Parcels'] ?? $item['Successful Delivery'] ?? $item['success_parcel'] ?? $item['delivered_parcel'] ?? $item['delivered'] ?? 0);
                $canceled = (int) ($item['Canceled Parcels'] ?? $item['Cancelled Parcels'] ?? $item['Canceled Delivery'] ?? $item['Cancelled Delivery'] ?? $item['cancelled_parcel'] ?? $item['canceled_parcel'] ?? $item['cancelled'] ?? 0);

                $total_parcel += $total;
                $total_delivered += $delivered;
                $total_cancel += $canceled;
            }
        }

        $rate = $total_parcel > 0 ? round(($total_delivered / $total_parcel) * 100, 2) : 0;

        return [
            'total_parcel' => $total_parcel,
            'total_delivered' => $total_delivered,
            'total_cancel' => $total_cancel,
            'delivery_rate' => $rate,
        ];
    }

    /**
     * Test connection to the Hoorin Fraud Check API.
     *
     * @param string|null $token
     * @param string|null $url
     * @return array
     */
    public function testConnection(?string $token = null, ?string $url = null): array
    {
        $fraud = Courierapi::where('type', 'fraud')->first();
        $apiToken = $token ?: ($fraud?->token);
        $endpoint = $this->getEndpointUrl($url ?: ($fraud?->url));

        if (empty($apiToken)) {
            return [
                'status' => 'error',
                'message' => 'API Token is empty. Please provide a valid token.',
            ];
        }

        try {
            $response = $this->buildHttpClient(15)->get($endpoint, [
                'apiKey' => $apiToken,
                'searchTerm' => '01711223344',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['Summaries']) || is_array($data)) {
                    return [
                        'status' => 'success',
                        'message' => 'Connected successfully! Hoorin Fraud Check API is responsive and ready.',
                    ];
                }
            }

            $errorMessage = $response->json('error') ?? $response->json('message') ?? ('Unexpected response status: ' . $response->status());

            return [
                'status' => 'error',
                'message' => 'Connected to server but received unexpected response: ' . $errorMessage,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Hoorin Fraud Check connection timeout/error: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
            ]);

            return [
                'status' => 'error',
                'message' => 'Connection timed out or network error. Please verify the Hoorin API server availability. (' . $e->getMessage() . ')',
            ];
        } catch (\Throwable $e) {
            Log::error('Hoorin Fraud Check general exception: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
            ]);

            return [
                'status' => 'error',
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ];
        }
    }
}