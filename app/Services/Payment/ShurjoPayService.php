<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use App\Models\PaymentGateway;

/**
 * Custom ShurjoPay Service — replaces shurjopayv2/laravel8
 * Direct HTTP API integration
 */
class ShurjoPayService
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $prefix;
    private string $returnUrl;
    private string $cancelUrl;

    public function __construct()
    {
        $gateway = PaymentGateway::where(['status' => 1, 'type' => 'shurjopay'])->first();

        if (!$gateway) {
            throw new \RuntimeException('ShurjoPay gateway not configured');
        }

        $this->baseUrl = $gateway->base_url;
        $this->username = $gateway->username;
        $this->password = $gateway->password;
        $this->prefix = $gateway->prefix;
        $this->returnUrl = $gateway->success_url;
        $this->cancelUrl = $gateway->return_url;
    }

    /**
     * Get authentication token
     */
    private function getToken(): string
    {
        $response = Http::post($this->baseUrl . '/api/get_token', [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('ShurjoPay authentication failed');
        }

        return $response->json('token');
    }

    /**
     * Create checkout / initiate payment
     */
    public function checkout(array $data): string
    {
        $token = $this->getToken();

        $payload = [
            'currency' => $data['currency'] ?? 'BDT',
            'amount' => $data['amount'],
            'order_id' => $data['order_id'],
            'discount_amount' => $data['discsount_amount'] ?? 0,
            'disc_percent' => $data['disc_percent'] ?? 0,
            'client_ip' => $data['client_ip'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'email' => $data['email'] ?? '',
            'customer_address' => $data['customer_address'],
            'customer_city' => $data['customer_city'],
            'customer_state' => $data['customer_state'] ?? '',
            'customer_postcode' => $data['customer_postcode'] ?? '',
            'customer_country' => $data['customer_country'] ?? 'BD',
            'value1' => $data['value1'] ?? '',
            'return_url' => $this->returnUrl,
            'cancel_url' => $this->cancelUrl,
        ];

        $response = Http::withToken($token)
            ->post($this->baseUrl . '/api/secret-pay', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('ShurjoPay checkout failed: ' . $response->body());
        }

        $result = $response->json();

        if (isset($result['checkout_url'])) {
            return redirect($result['checkout_url']);
        }

        throw new \RuntimeException('ShurjoPay: No checkout URL returned');
    }

    /**
     * Verify payment
     */
    public function verify(string $orderId): array
    {
        $token = $this->getToken();

        $response = Http::withToken($token)
            ->post($this->baseUrl . '/api/verify', [
                'order_id' => $orderId,
            ]);

        if (!$response->successful()) {
            return [
                ['sp_code' => 0, 'sp_message' => 'Verification failed'],
            ];
        }

        return $response->json();
    }
}
