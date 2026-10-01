<?php

namespace App\Services;

use App\Models\EcomPixel;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MetaConversionsApiService
{
    protected string $graphApiVersion = 'v19.0';
    protected string $baseUrl = 'https://graph.facebook.com';

    /**
     * Send an event or batch of events to Meta Conversions API
     */
    public function sendEvent(
        string $eventName,
        array $userData = [],
        array $customData = [],
        ?string $eventId = null,
        ?string $eventSourceUrl = null,
        ?EcomPixel $specificPixel = null
    ): array {
        $pixels = $specificPixel ? collect([$specificPixel]) : EcomPixel::where('status', 1)
            ->where('capi_status', 1)
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->get();

        if ($pixels->isEmpty()) {
            $fallbackToken = config('services.meta.access_token') ?: env('META_CAPI_ACCESS_TOKEN');
            if ($fallbackToken) {
                $fallbackPixel = EcomPixel::where('status', 1)->first() ?: new EcomPixel([
                    'code' => config('services.meta.pixel_id', '2183884522084882'),
                    'status' => 1,
                    'capi_status' => 1,
                ]);
                $fallbackPixel->access_token = $fallbackToken;
                $fallbackPixel->test_event_code = $fallbackPixel->test_event_code ?: (config('services.meta.test_event_code') ?: env('META_TEST_EVENT_CODE'));
                $pixels = collect([$fallbackPixel]);
            }
        }

        if ($pixels->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No active Facebook Pixel with Conversions API access token configured.',
                'events_sent' => 0,
            ];
        }

        $eventId = $eventId ?: 'evt_' . (string) Str::uuid();
        $eventSourceUrl = $eventSourceUrl ?: (request()->fullUrl() ?? config('app.url'));
        $formattedUserData = $this->buildUserData($userData);

        $results = [];
        foreach ($pixels as $pixel) {
            $pixelId = trim($pixel->code);
            $accessToken = trim($pixel->access_token);
            $testEventCode = trim($pixel->test_event_code ?? '');

            $eventPayload = [
                'event_name' => $eventName,
                'event_time' => time(),
                'event_id' => $eventId,
                'event_source_url' => $eventSourceUrl,
                'action_source' => 'website',
                'user_data' => $formattedUserData,
            ];

            if (!empty($customData)) {
                $eventPayload['custom_data'] = $customData;
            }

            $requestBody = [
                'data' => [$eventPayload],
            ];

            if (!empty($testEventCode)) {
                $requestBody['test_event_code'] = $testEventCode;
            }

            try {
                $endpoint = "{$this->baseUrl}/{$this->graphApiVersion}/{$pixelId}/events?access_token=" . urlencode($accessToken);
                $response = Http::withToken($accessToken)
                    ->timeout(10)
                    ->post($endpoint, array_merge($requestBody, [
                        'access_token' => $accessToken,
                    ]));

                $responseBody = $response->json();
                $isSuccess = $response->successful() && ($responseBody['events_received'] ?? 0) > 0;

                if (!$isSuccess) {
                    Log::warning("Meta CAPI Event [{$eventName}] Warning/Failure for Pixel [{$pixelId}]:", [
                        'status' => $response->status(),
                        'response' => $responseBody,
                        'event_id' => $eventId,
                    ]);
                }

                $results[$pixelId] = [
                    'success' => $isSuccess,
                    'status' => $response->status(),
                    'response' => $responseBody,
                    'event_id' => $eventId,
                ];
            } catch (\Throwable $e) {
                Log::error("Meta CAPI Exception for Pixel [{$pixelId}]: " . $e->getMessage(), [
                    'event' => $eventName,
                    'event_id' => $eventId,
                ]);

                $results[$pixelId] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'event_id' => $eventId,
                ];
            }
        }

        return [
            'success' => collect($results)->contains('success', true),
            'results' => $results,
            'event_id' => $eventId,
        ];
    }

    /**
     * Track Purchase Event for Order
     */
    public function trackPurchase(Order $order, ?string $eventId = null, ?string $eventSourceUrl = null, array $extraUserData = []): array
    {
        $order->loadMissing(['orderdetails', 'shipping', 'customer']);

        $shipping = $order->shipping;
        $customer = $order->customer;

        $userData = array_merge([
            'phone' => $shipping->phone ?? ($customer->phone ?? null),
            'email' => $shipping->email ?? ($customer->email ?? null),
            'first_name' => $shipping->name ?? ($customer->name ?? null),
            'city' => $shipping->area ?? null,
            'address' => $shipping->address ?? null,
        ], $extraUserData);

        $contents = [];
        $contentIds = [];
        $numItems = 0;

        foreach ($order->orderdetails as $detail) {
            $contents[] = [
                'id' => (string) ($detail->product_id ?? $detail->id),
                'quantity' => (int) ($detail->qty ?? 1),
                'item_price' => (float) ($detail->sale_price ?? 0),
            ];
            $contentIds[] = (string) ($detail->product_id ?? $detail->id);
            $numItems += (int) ($detail->qty ?? 1);
        }

        $customData = [
            'currency' => 'BDT',
            'value' => (float) ($order->amount ?? 0),
            'content_type' => 'product',
            'contents' => $contents,
            'content_ids' => $contentIds,
            'num_items' => $numItems,
            'order_id' => (string) ($order->invoice_id ?? $order->id),
        ];

        $eventId = $eventId ?: 'order_' . ($order->invoice_id ?? $order->id);

        return $this->sendEvent('Purchase', $userData, $customData, $eventId, $eventSourceUrl);
    }

    /**
     * Track AddToCart Event
     */
    public function trackAddToCart(array $item, ?string $eventId = null, ?string $eventSourceUrl = null, array $userData = []): array
    {
        $price = (float) ($item['price'] ?? $item['new_price'] ?? 0);
        $qty = (int) ($item['qty'] ?? 1);
        $productId = (string) ($item['id'] ?? $item['product_id'] ?? '');

        $customData = [
            'currency' => 'BDT',
            'value' => $price * $qty,
            'content_type' => 'product',
            'content_name' => $item['name'] ?? '',
            'content_ids' => [$productId],
            'contents' => [
                [
                    'id' => $productId,
                    'quantity' => $qty,
                    'item_price' => $price,
                ]
            ],
            'num_items' => $qty,
        ];

        return $this->sendEvent('AddToCart', $userData, $customData, $eventId, $eventSourceUrl);
    }

    /**
     * Track InitiateCheckout Event
     */
    public function trackInitiateCheckout(array $cartItems, float $totalAmount, ?string $eventId = null, ?string $eventSourceUrl = null, array $userData = []): array
    {
        $contents = [];
        $contentIds = [];
        $numItems = 0;

        foreach ($cartItems as $item) {
            $id = (string) ($item['id'] ?? '');
            $qty = (int) ($item['qty'] ?? 1);
            $price = (float) ($item['price'] ?? 0);

            $contents[] = [
                'id' => $id,
                'quantity' => $qty,
                'item_price' => $price,
            ];
            $contentIds[] = $id;
            $numItems += $qty;
        }

        $customData = [
            'currency' => 'BDT',
            'value' => $totalAmount,
            'content_type' => 'product',
            'contents' => $contents,
            'content_ids' => $contentIds,
            'num_items' => $numItems,
        ];

        return $this->sendEvent('InitiateCheckout', $userData, $customData, $eventId, $eventSourceUrl);
    }

    /**
     * Track ViewContent Event
     */
    public function trackViewContent(array $product, ?string $eventId = null, ?string $eventSourceUrl = null, array $userData = []): array
    {
        $productId = (string) ($product['id'] ?? '');
        $price = (float) ($product['new_price'] ?? $product['price'] ?? 0);

        $customData = [
            'currency' => 'BDT',
            'value' => $price,
            'content_type' => 'product',
            'content_name' => $product['name'] ?? '',
            'content_ids' => [$productId],
            'contents' => [
                [
                    'id' => $productId,
                    'quantity' => 1,
                    'item_price' => $price,
                ]
            ],
        ];

        return $this->sendEvent('ViewContent', $userData, $customData, $eventId, $eventSourceUrl);
    }

    /**
     * Build User Data with SHA-256 Hashing & Auto-detected Cookies/IP/UserAgent
     */
    public function buildUserData(array $input = []): array
    {
        $userData = [];

        // Hashed Email
        $email = $input['email'] ?? $input['em'] ?? null;
        if (!empty($email)) {
            $userData['em'] = [$this->hashValue(strtolower(trim($email)))];
        }

        // Hashed Phone
        $phone = $input['phone'] ?? $input['ph'] ?? null;
        if (!empty($phone)) {
            $normalizedPhone = $this->normalizePhone($phone);
            if ($normalizedPhone) {
                $userData['ph'] = [$this->hashValue($normalizedPhone)];
            }
        }

        // Hashed First Name / Full Name
        $name = $input['name'] ?? $input['first_name'] ?? $input['fn'] ?? null;
        if (!empty($name)) {
            $nameParts = explode(' ', trim($name), 2);
            $userData['fn'] = [$this->hashValue(strtolower(trim($nameParts[0])))];
            if (!empty($nameParts[1])) {
                $userData['ln'] = [$this->hashValue(strtolower(trim($nameParts[1])))];
            }
        }

        if (!empty($input['last_name']) || !empty($input['ln'])) {
            $lastName = $input['last_name'] ?? $input['ln'];
            $userData['ln'] = [$this->hashValue(strtolower(trim($lastName)))];
        }

        // Hashed City
        $city = $input['city'] ?? $input['ct'] ?? null;
        if (!empty($city)) {
            $userData['ct'] = [$this->hashValue(strtolower(trim($city)))];
        }

        // Hashed Country (Default BD)
        $country = $input['country'] ?? 'bd';
        $userData['country'] = [$this->hashValue(strtolower(trim($country)))];

        // IP Address
        $ip = $input['client_ip_address'] ?? request()->ip();
        if (!empty($ip) && $ip !== '127.0.0.1' && $ip !== '::1') {
            $userData['client_ip_address'] = $ip;
        } elseif (!empty($ip)) {
            $userData['client_ip_address'] = $ip;
        }

        // User Agent
        $userAgent = $input['client_user_agent'] ?? request()->userAgent();
        if (!empty($userAgent)) {
            $userData['client_user_agent'] = $userAgent;
        }

        // Facebook Browser Cookie (_fbp)
        $fbp = $input['fbp'] ?? request()->cookie('_fbp');
        if (!empty($fbp)) {
            $userData['fbp'] = $fbp;
        }

        // Facebook Click ID Cookie (_fbc)
        $fbc = $input['fbc'] ?? request()->cookie('_fbc');
        if (!empty($fbc)) {
            $userData['fbc'] = $fbc;
        } elseif (request()->has('fbclid')) {
            $userData['fbc'] = 'fb.1.' . time() . '.' . request()->get('fbclid');
        }

        return $userData;
    }

    /**
     * Normalize Phone Number to E.164-like standard (BD default: 8801XXXXXXXXX)
     */
    public function normalizePhone(string $phone): ?string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (empty($clean)) {
            return null;
        }

        if (Str::startsWith($clean, '880')) {
            return $clean;
        }

        if (Str::startsWith($clean, '01') && strlen($clean) === 11) {
            return '88' . $clean;
        }

        if (Str::startsWith($clean, '1') && strlen($clean) === 10) {
            return '880' . $clean;
        }

        return $clean;
    }

    /**
     * SHA-256 Hashing helper according to Meta Specs
     */
    public function hashValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return hash('sha256', strtolower(trim($value)));
    }

    /**
     * Test connection to Meta Graph API for given credentials
     */
    public function testConnection(string $pixelId, string $accessToken, ?string $testCode = null): array
    {
        $pixel = new EcomPixel([
            'code' => $pixelId,
            'access_token' => $accessToken,
            'test_event_code' => $testCode,
            'status' => 1,
            'capi_status' => 1,
        ]);

        return $this->sendEvent(
            'PageView',
            ['email' => 'test@example.com'],
            ['test_connection' => true],
            'test_conn_' . (string) Str::uuid(),
            config('app.url'),
            $pixel
        );
    }
}