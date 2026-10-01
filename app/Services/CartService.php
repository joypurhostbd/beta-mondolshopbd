<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Custom Cart Service — replaces olimortimer/laravelshoppingcart
 * Session-based cart with multiple instance support
 */
class CartService
{
    private string $instance;

    public function __construct(string $instance = 'shopping')
    {
        $this->instance = $instance;
    }

    public static function instance(string $instance): self
    {
        return new self($instance);
    }

    private function getSessionKey(): string
    {
        return "cart.{$this->instance}";
    }

    private function getItems(): array
    {
        return Session::get($this->getSessionKey(), []);
    }

    private function saveItems(array $items): void
    {
        Session::put($this->getSessionKey(), $items);
    }

    /**
     * Add item to cart
     */
    public function add(array $attributes): object
    {
        $items = $this->getItems();
        $rowId = $this->generateRowId($attributes['id'], $attributes['options'] ?? []);

        if (isset($items[$rowId])) {
            $items[$rowId]['qty'] += ($attributes['qty'] ?? 1);
        } else {
            $items[$rowId] = [
                'rowId' => $rowId,
                'id' => $attributes['id'],
                'name' => $attributes['name'],
                'qty' => $attributes['qty'] ?? 1,
                'price' => (float) $attributes['price'],
                'options' => $attributes['options'] ?? [],
            ];
        }

        $this->saveItems($items);

        $obj = (object) $items[$rowId];
        if (isset($obj->options) && is_array($obj->options)) {
            $obj->options = (object) $obj->options;
        }

        return $obj;
    }

    /**
     * Update item quantity
     */
    public function update(string $rowId, int|float|array $qty): object
    {
        $items = $this->getItems();

        if (is_array($qty)) {
            // Update attributes
            if (isset($items[$rowId])) {
                $items[$rowId] = array_merge($items[$rowId], $qty);
            }
        } else {
            if ($qty <= 0) {
                unset($items[$rowId]);
            } elseif (isset($items[$rowId])) {
                $items[$rowId]['qty'] = $qty;
            }
        }

        $this->saveItems($items);

        $obj = (object) ($items[$rowId] ?? []);
        if (isset($obj->options) && is_array($obj->options)) {
            $obj->options = (object) $obj->options;
        }

        return $obj;
    }

    /**
     * Remove item from cart
     */
    public function remove(string $rowId): void
    {
        $items = $this->getItems();
        unset($items[$rowId]);
        $this->saveItems($items);
    }

    /**
     * Get single item
     */
    public function get(string $rowId): ?object
    {
        $items = $this->getItems();

        if (! isset($items[$rowId])) {
            return null;
        }

        $obj = (object) $items[$rowId];
        if (isset($obj->options) && is_array($obj->options)) {
            $obj->options = (object) $obj->options;
        }

        return $obj;
    }

    /**
     * Get all items as collection
     */
    public function content(): Collection
    {
        $items = $this->getItems();

        return collect($items)->map(function ($item) {
            $obj = (object) $item;
            if (isset($obj->options) && is_array($obj->options)) {
                $obj->options = (object) $obj->options;
            }
            $obj->subtotal = ($obj->price ?? 0) * ($obj->qty ?? 1);

            return $obj;
        });
    }

    /**
     * Get total item count
     */
    public function count(): int
    {
        $items = $this->getItems();
        $total = 0;
        foreach ($items as $item) {
            $total += $item['qty'];
        }

        return $total;
    }

    /**
     * Get subtotal
     */
    public function subtotal(): float
    {
        $items = $this->getItems();
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['qty'];
        }

        return $total;
    }

    /**
     * Get total
     */
    public function total(): float
    {
        return $this->subtotal();
    }

    /**
     * Destroy cart
     */
    public function destroy(): void
    {
        Session::forget($this->getSessionKey());
    }

    /**
     * Generate unique row ID
     */
    private function generateRowId(string $id, array $options): string
    {
        $optionString = json_encode($options, JSON_THROW_ON_ERROR);

        return md5($id.$optionString);
    }
}
