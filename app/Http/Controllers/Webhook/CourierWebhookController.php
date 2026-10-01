<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Courierapi;
use Modules\Shipping\Application\Actions\HandleCourierWebhookAction;

class CourierWebhookController extends Controller
{
    public function __construct(
        private HandleCourierWebhookAction $webhookAction
    ) {}

    public function handle(string $provider, Request $request): JsonResponse
    {
        $courier = Courierapi::where('type', strtolower($provider))->first();

        // Optional token authentication if merchant configured a webhook token/secret
        if ($courier && !empty($courier->token)) {
            $expectedToken = trim($courier->token);
            $providedToken = $request->bearerToken()
                ?? $request->header('X-Webhook-Token')
                ?? $request->header('Secret-Key')
                ?? $request->input('token')
                ?? $request->input('secret_key');

            if (!$providedToken || !hash_equals($expectedToken, trim($providedToken))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized webhook token',
                ], 401);
            }
        }

        $payload = $request->input();

        $result = $this->webhookAction->execute($provider, $payload);

        return response()->json([
            'success' => true,
            'provider' => $provider,
            'result' => $result,
        ]);
    }

    /**
     * Web Cron trigger endpoint for polling live courier delivery status.
     */
    public function cronSync(Request $request, \Modules\Shipping\Application\Actions\SyncCourierOrderStatusAction $syncAction): JsonResponse
    {
        $cronSecret = config('services.courier_cron_secret', env('COURIER_CRON_SECRET', 'mondolshopbd_courier_cron_secret'));
        $providedKey = $request->query('key') ?? $request->header('X-Cron-Key') ?? $request->bearerToken();

        if (empty($providedKey) || !hash_equals((string) $cronSecret, (string) $providedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized cron request. Valid key parameter required.',
            ], 401);
        }

        $limit = min((int) ($request->query('limit', 50)), 100);
        $provider = strtolower((string) $request->query('courier', 'steadfast'));
        $terminalStatuses = ['delivered', 'cancelled', 'returned', 'return'];

        $query = \App\Models\Order::query()
            ->where(function ($q) {
                $q->whereNotNull('consignment_id')->where('consignment_id', '!=', '')
                  ->orWhereNotNull('tracking_code')->where('tracking_code', '!=', '');
            })
            ->where(function ($q) use ($terminalStatuses) {
                $q->whereNull('courier_status')
                  ->orWhereNotIn('courier_status', $terminalStatuses);
            });

        if ($provider !== 'all') {
            $query->where(function ($q) use ($provider) {
                $q->whereNull('courier_name')
                  ->orWhere('courier_name', $provider);
            });
        }

        $orders = $query->latest()->limit($limit)->get();

        $checked = 0;
        $updated = 0;
        $failed = 0;

        foreach ($orders as $order) {
            $checked++;
            try {
                $res = $syncAction->execute($order);
                if ($res['success']) {
                    if (!empty($res['status_changed'])) {
                        $updated++;
                    }
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Courier status sync completed. {$checked} checked, {$updated} updated, {$failed} failed.",
            'checked' => $checked,
            'updated' => $updated,
            'failed' => $failed,
        ]);
    }
}
