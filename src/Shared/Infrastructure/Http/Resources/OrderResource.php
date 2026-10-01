<?php

namespace Shared\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Shared\Domain\Enums\OrderStatusEnum;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        $statusEnum = $this->order_status instanceof OrderStatusEnum
            ? $this->order_status
            : (OrderStatusEnum::tryFrom((int) $this->order_status) ?? OrderStatusEnum::PENDING);

        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id ?? (string) $this->id,
            'customer' => [
                'name' => $this->customer_name ?? $this->name ?? '',
                'phone' => $this->customer_phone ?? $this->phone ?? '',
                'address' => $this->shipping_address ?? $this->address ?? '',
            ],
            'status' => [
                'code' => $statusEnum->value,
                'label' => $statusEnum->label(),
                'badge' => $statusEnum->badgeColor(),
            ],
            'pricing' => [
                'subtotal' => (float) ($this->subtotal ?? 0),
                'shipping_charge' => (float) ($this->shipping_charge ?? 0),
                'discount' => (float) ($this->discount ?? 0),
                'total' => (float) ($this->amount ?? $this->total_amount ?? $this->total ?? 0),
            ],
            'courier' => [
                'name' => $this->courier_name ?? null,
                'tracking_code' => $this->courier_status ?? $this->tracking_code ?? null,
            ],
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}