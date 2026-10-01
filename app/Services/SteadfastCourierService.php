<?php

namespace App\Services;

use App\Models\Courierapi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteadfastCourierService
{
    protected ?string $baseUrl = null;
    protected ?string $apiKey = null;
    protected ?string $secretKey = null;
    protected bool $isConfigured = false;

    public function __construct(?Courierapi $courier = null)
    {
        if ($courier === null) {
            $courier = Courierapi::where('type', 'steadfast')->first();
        }

        if ($courier && !empty($courier->api_key) && !empty($courier->secret_key)) {
            $this->apiKey = trim($courier->api_key);
            $this->secretKey = trim($courier->secret_key);
            $this->baseUrl = rtrim($courier->url ?: 'https://portal.steadfast.com.bd/api/v1', '/');
            $this->isConfigured = true;
        }
    }

    public function withConfig(string $apiKey, string $secretKey, ?string $baseUrl = null): self
    {
        $clone = clone $this;
        $clone->apiKey = trim($apiKey);
        $clone->secretKey = trim($secretKey);
        $clone->baseUrl = rtrim($baseUrl ?: 'https://portal.steadfast.com.bd/api/v1', '/');
        $clone->isConfigured = !empty($clone->apiKey) && !empty($clone->secretKey);

        return $clone;
    }

    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl ?: 'https://portal.steadfast.com.bd/api/v1';
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Create a single order in SteadFast courier.
     *
     * @param array $data [invoice, recipient_name, recipient_phone, recipient_address, cod_amount, note]
     * @return array
     */
    public function placeOrder(array $data): array
    {
        if (!$this->isConfigured) {
            return [
                'status' => 400,
                'message' => 'SteadFast Courier API credentials are not configured.',
            ];
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(20)
                ->post($this->baseUrl . '/create_order', [
                    'invoice' => (string) ($data['invoice'] ?? ''),
                    'recipient_name' => (string) ($data['recipient_name'] ?? ''),
                    'recipient_phone' => (string) ($data['recipient_phone'] ?? ''),
                    'recipient_address' => (string) ($data['recipient_address'] ?? ''),
                    'cod_amount' => (float) ($data['cod_amount'] ?? 0),
                    'note' => (string) ($data['note'] ?? ''),
                ]);

            return $response->json() ?? [
                'status' => $response->status(),
                'message' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('SteadFast placeOrder exception: ' . $e->getMessage(), ['data' => $data]);
            return [
                'status' => 500,
                'message' => 'Network error communicating with SteadFast: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Bulk create orders in SteadFast courier.
     *
     * @param array $orders Array of order arrays
     * @return array
     */
    public function bulkCreateOrders(array $orders): array
    {
        if (!$this->isConfigured) {
            return [
                'status' => 400,
                'message' => 'SteadFast Courier API credentials are not configured.',
            ];
        }

        try {
            $formattedData = [];
            foreach ($orders as $order) {
                $formattedData[] = [
                    'invoice' => (string) ($order['invoice'] ?? ''),
                    'recipient_name' => (string) ($order['recipient_name'] ?? ''),
                    'recipient_phone' => (string) ($order['recipient_phone'] ?? ''),
                    'recipient_address' => (string) ($order['recipient_address'] ?? ''),
                    'cod_amount' => (float) ($order['cod_amount'] ?? 0),
                    'note' => (string) ($order['note'] ?? ''),
                ];
            }

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post($this->baseUrl . '/create_order/bulk-order', [
                    'data' => json_encode($formattedData),
                ]);

            return $response->json() ?? [
                'status' => $response->status(),
                'message' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('SteadFast bulkCreateOrders exception: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'Network error communicating with SteadFast: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check delivery status by consignment ID.
     */
    public function checkDeliveryStatusByConsignmentId(string|int $id): array
    {
        return $this->get('/status_by_cid/' . urlencode((string) $id));
    }

    /**
     * Check delivery status by merchant invoice ID.
     */
    public function checkDeliveryStatusByInvoice(string|int $invoiceId): array
    {
        return $this->get('/status_by_invoice/' . urlencode((string) $invoiceId));
    }

    /**
     * Check delivery status by SteadFast tracking code.
     */
    public function checkDeliveryStatusByTrackingCode(string $trackingCode): array
    {
        return $this->get('/status_by_trackingcode/' . urlencode($trackingCode));
    }

    /**
     * Retrieve merchant's current account balance from SteadFast.
     */
    public function getCurrentBalance(): array
    {
        return $this->get('/get_balance');
    }

    /**
     * Test API connection and fetch current balance.
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'message' => 'API Key and Secret Key are required.',
            ];
        }

        $balanceResponse = $this->getCurrentBalance();

        if (isset($balanceResponse['status']) && (int) $balanceResponse['status'] === 200) {
            return [
                'success' => true,
                'message' => 'Connected successfully to SteadFast Courier API.',
                'current_balance' => $balanceResponse['current_balance'] ?? 0,
            ];
        }

        return [
            'success' => false,
            'message' => $balanceResponse['message'] ?? 'Could not authenticate with SteadFast. Please check API Key and Secret Key.',
            'raw' => $balanceResponse,
        ];
    }

    /**
     * Internal GET helper.
     */
    protected function get(string $endpoint): array
    {
        if (!$this->isConfigured) {
            return [
                'status' => 400,
                'message' => 'SteadFast Courier API credentials are not configured.',
            ];
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->get($this->baseUrl . $endpoint);

            return $response->json() ?? [
                'status' => $response->status(),
                'message' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error("SteadFast GET {$endpoint} exception: " . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'Network error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Standard SteadFast API headers.
     */
    protected function getHeaders(): array
    {
        return [
            'Api-Key' => $this->apiKey,
            'Secret-Key' => $this->secretKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
