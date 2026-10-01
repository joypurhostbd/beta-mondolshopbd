<?php

namespace Shared\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $unitPrice = (float) ($this->price ?? $this->unit_price ?? 0);
        $qty = (int) ($this->qty ?? $this->quantity ?? 1);

        return [
            'row_id' => (string) ($this->rowId ?? $this->row_id ?? $this->id ?? ''),
            'product_id' => (int) ($this->product_id ?? $this->id ?? 0),
            'name' => $this->name ?? '',
            'price' => $unitPrice,
            'quantity' => $qty,
            'subtotal' => round($unitPrice * $qty, 2),
            'options' => [
                'size' => $this->options?->size ?? $this->size ?? null,
                'color' => $this->options?->color ?? $this->color ?? null,
                'image' => $this->options?->image ?? $this->image ?? null,
            ],
        ];
    }
}