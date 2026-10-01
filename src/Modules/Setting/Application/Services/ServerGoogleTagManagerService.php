<?php

namespace Modules\Setting\Application\Services;

use App\Models\GoogleTagManager;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ServerGoogleTagManagerService
{
    /**
     * Send an event to all active Server-Side GTM containers.
     */
    public function sendEvent(
        string $eventName,
        array $eventParams = [],
        array $userData = [],
        ?string $eventId = null,
        ?string $clientId = null
    ): array {
        $containers = GoogleTagManager::with('eventConfigs')
            ->active()
            ->where('is_server_side', 1)
            ->whereNotNull('server_container_url')
            ->where('server_container_url', '!=', '')
            ->get();

        if ($containers->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No active Server-Side GTM container configured.',
                'dispatched_count' => 0,
            ];
        }

        $clientId = $clientId ?: $this->extractClientId();
        $eventId = $eventId ?: 'evt_' . (string) Str::uuid();
        $eventParams['event_id'] = $eventId;

        $formattedUserData = $this->buildEnhancedUserData($userData);
        $clientIp = request()->ip() ?? '127.0.0.1';
        $userAgent = request()->userAgent() ?? 'MondolShopBD Server/1.0';

        $dispatched = 0;
        $results = [];

        foreach ($containers as $container) {
            // Check if server-side tracking is enabled for this specific event
            if (!$container->isServerEventEnabled($this->normalizeEventKey($eventName))) {
                continue;
            }

            $serverUrl = rtrim($container->server_container_url, '/');
            $endpoint = $serverUrl . '/mp/collect';

            $queryParams = [];
            if (!empty($container->measurement_id)) {
                $queryParams['measurement_id'] = $container->measurement_id;
            }
            if (!empty($container->api_secret)) {
                $queryParams['api_secret'] = $container->api_secret;
            }

            if (!empty($queryParams)) {
                $endpoint .= '?' . http_build_query($queryParams);
            }

            $payload = [
                'client_id' => $clientId,
                'events' => [
                    [
                        'name' => $eventName,
                        'params' => $eventParams,
                    ],
                ],
            ];

            if (!empty($formattedUserData)) {
                $payload['user_data'] = $formattedUserData;
            }

            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-Forwarded-For' => $clientIp,
                        'User-Agent' => $userAgent,
                    ])
                    ->post($endpoint, $payload);

                $success = $response->successful();
                if ($success) {
                    $dispatched++;
                } else {
                    Log::warning("Server-Side GTM Event [{$eventName}] returned non-200 for [{$container->code}]:", [
                        'status' => $response->status(),
                        'endpoint' => $endpoint,
                    ]);
                }

                $results[] = [
                    'container' => $container->code,
                    'status' => $response->status(),
                    'success' => $success,
                ];
            } catch (\Throwable $e) {
                Log::error("Server-Side GTM Dispatch Exception for [{$eventName}] on [{$container->code}]: " . $e->getMessage());
                $results[] = [
                    'container' => $container->code,
                    'error' => $e->getMessage(),
                    'success' => false,
                ];
            }
        }

        return [
            'success' => $dispatched > 0,
            'dispatched_count' => $dispatched,
            'results' => $results,
            'event_id' => $eventId,
        ];
    }

    /**
     * Track Purchase Server-Side
     */
    public function trackPurchase(Order $order, ?string $eventId = null, array $userData = []): array
    {
        $items = [];
        if ($order->orderdetails) {
            foreach ($order->orderdetails as $detail) {
                $items[] = [
                    'item_id' => (string) ($detail->product_id ?? $detail->id),
                    'item_name' => (string) ($detail->product_name ?? 'Product'),
                    'price' => (float) ($detail->sale_price ?? 0),
                    'quantity' => (int) ($detail->qty ?? 1),
                ];
            }
        }

        $eventParams = [
            'transaction_id' => (string) ($order->invoice_id ?? $order->id),
            'value' => (float) ($order->amount ?? 0),
            'currency' => 'BDT',
            'shipping' => (float) ($order->shipping_charge ?? 0),
            'discount' => (float) ($order->discount ?? 0),
            'items' => $items,
        ];

        $eventId = $eventId ?: 'order_' . ($order->invoice_id ?? $order->id);

        if (empty($userData) && $order->shipping) {
            $userData = [
                'phone' => $order->shipping->phone ?? null,
                'name' => $order->shipping->name ?? null,
                'address' => $order->shipping->address ?? null,
                'city' => $order->shipping->area ?? 'Dhaka',
            ];
        }

        return $this->sendEvent('purchase', $eventParams, $userData, $eventId);
    }

    /**
     * Track AddToCart Server-Side
     */
    public function trackAddToCart(array $item, ?string $eventId = null, array $userData = []): array
    {
        $price = (float) ($item['price'] ?? $item['new_price'] ?? 0);
        $qty = (int) ($item['qty'] ?? 1);

        $eventParams = [
            'currency' => 'BDT',
            'value' => $price * $qty,
            'items' => [
                [
                    'item_id' => (string) ($item['id'] ?? $item['product_id'] ?? ''),
                    'item_name' => (string) ($item['name'] ?? ''),
                    'price' => $price,
                    'quantity' => $qty,
                ],
            ],
        ];

        return $this->sendEvent('add_to_cart', $eventParams, $userData, $eventId);
    }

    /**
     * Track BeginCheckout Server-Side
     */
    public function trackInitiateCheckout(array $cartItems, float $totalAmount, ?string $eventId = null, array $userData = []): array
    {
        $items = [];
        foreach ($cartItems as $item) {
            $items[] = [
                'item_id' => (string) ($item['id'] ?? ''),
                'item_name' => (string) ($item['name'] ?? ''),
                'price' => (float) ($item['price'] ?? 0),
                'quantity' => (int) ($item['qty'] ?? 1),
            ];
        }

        $eventParams = [
            'currency' => 'BDT',
            'value' => $totalAmount,
            'items' => $items,
        ];

        return $this->sendEvent('begin_checkout', $eventParams, $userData, $eventId);
    }

    /**
     * Track ViewItem Server-Side
     */
    public function trackViewContent(array $product, ?string $eventId = null, array $userData = []): array
    {
        $price = (float) ($product['new_price'] ?? $product['price'] ?? 0);

        $eventParams = [
            'currency' => 'BDT',
            'value' => $price,
            'items' => [
                [
                    'item_id' => (string) ($product['id'] ?? ''),
                    'item_name' => (string) ($product['name'] ?? ''),
                    'price' => $price,
                    'quantity' => 1,
                ],
            ],
        ];

        return $this->sendEvent('view_item', $eventParams, $userData, $eventId);
    }

    /**
     * Extract Google Analytics client ID from _ga cookie or generate UUID
     */
    protected function extractClientId(): string
    {
        $gaCookie = request()->cookie('_ga');
        if (!empty($gaCookie) && preg_match('/GA\d+\.\d+\.(\d+\.\d+)/', $gaCookie, $matches)) {
            return $matches[1];
        }

        return (string) Str::uuid();
    }

    /**
     * Build Google Enhanced Conversions user data payload with SHA-256 hashing.
     */
    protected function buildEnhancedUserData(array $raw): array
    {
        $data = [];

        if (!empty($raw['email'])) {
            $cleanEmail = strtolower(trim($raw['email']));
            $data['sha256_email_address'] = hash('sha256', $cleanEmail);
        }

        if (!empty($raw['phone'])) {
            $cleanPhone = preg_replace('/[^\d+]/', '', $raw['phone']);
            // Normalize Bangladesh phone format: 017XXXXXXXX -> +88017XXXXXXXX
            if (str_starts_with($cleanPhone, '01')) {
                $cleanPhone = '+88' . $cleanPhone;
            } elseif (str_starts_with($cleanPhone, '8801')) {
                $cleanPhone = '+' . $cleanPhone;
            }
            $data['sha256_phone_number'] = hash('sha256', $cleanPhone);
        }

        $address = [];
        if (!empty($raw['name'])) {
            $parts = explode(' ', trim($raw['name']), 2);
            $address['first_name'] = hash('sha256', strtolower(trim($parts[0])));
            if (!empty($parts[1])) {
                $address['last_name'] = hash('sha256', strtolower(trim($parts[1])));
            }
        }

        if (!empty($raw['city'])) {
            $address['city'] = trim($raw['city']);
        }
        $address['country'] = 'BD';

        if (!empty($address)) {
            $data['address'] = $address;
        }

        return $data;
    }

    protected function normalizeEventKey(string $name): string
    {
        return match (strtolower($name)) {
            'page_view', 'pageview' => 'page_view',
            'view_item', 'viewcontent' => 'view_content',
            'add_to_cart', 'addtocart' => 'add_to_cart',
            'begin_checkout', 'initiatecheckout' => 'initiate_checkout',
            'purchase' => 'purchase',
            default => strtolower($name),
        };
    }
}
