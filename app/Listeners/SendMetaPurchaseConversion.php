<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SendMetaCapiEventJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendMetaPurchaseConversion
{
    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        try {
            $order = $event->order;
            if (!$order) {
                return;
            }

            // Only dispatch direct Meta CAPI if an active pixel with an access token is explicitly configured.
            // When tracking via Google Tag Manager / sGTM, GTM handles server conversions to prevent double data.
            $hasActiveCapiPixel = \App\Models\EcomPixel::where('status', 1)
                ->where('capi_status', 1)
                ->whereNotNull('access_token')
                ->where('access_token', '!=', '')
                ->exists();

            if (!$hasActiveCapiPixel && empty(config('services.meta.access_token')) && empty(env('META_CAPI_ACCESS_TOKEN'))) {
                return;
            }

            $eventId = 'order_' . ($order->invoice_id ?? $order->id);
            $eventSourceUrl = request()->fullUrl() ?? config('app.url') . '/order-success/' . ($order->id ?? '');

            $order->loadMissing(['shipping', 'customer']);

            $fbp = request()->cookie('_fbp');
            $fbc = request()->cookie('_fbc') ?: (request()->has('fbclid') ? 'fb.1.' . time() . '.' . request()->get('fbclid') : null);

            $userData = [
                'fbp' => $fbp,
                'fbc' => $fbc,
                'client_ip_address' => request()->ip(),
                'client_user_agent' => request()->userAgent(),
                'phone' => $order->shipping?->phone ?? $order->customer?->phone,
                'email' => $order->shipping?->email ?? $order->customer?->email,
                'first_name' => $order->shipping?->name ?? $order->customer?->name,
                'city' => $order->shipping?->area,
                'address' => $order->shipping?->address,
            ];

            SendMetaCapiEventJob::dispatch(
                'Purchase',
                $userData,
                [],
                $eventId,
                $eventSourceUrl,
                $order->id
            );
        } catch (\Throwable $e) {
            Log::error("SendMetaPurchaseConversion Listener Error: " . $e->getMessage());
        }
    }
}