<?php

namespace Shared\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => (float) ($this->new_price ?? $this->regular_price ?? 0),
            'old_price' => $this->old_price ? (float) $this->old_price : null,
            'stock' => (int) ($this->stock ?? 0),
            'image' => $this->image_url ?? $this->image_one ?? null,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null,
            'is_active' => (bool) ($this->status ?? true),
        ];
    }
}