<?php

namespace Modules\Order\Infrastructure\Repositories;

use Illuminate\Support\Facades\Cache;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;
use Modules\Order\Domain\Entities\CartEntity;
use Modules\Order\Domain\Entities\CartItemEntity;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\Quantity;

class RedisCartRepository implements CartRepositoryInterface
{
    private const PREFIX = 'mondol_cart:';

    public function get(string $cartKey): CartEntity
    {
        $data = Cache::get(self::PREFIX . $cartKey);

        if (!$data || !is_array($data)) {
            return new CartEntity($cartKey, []);
        }

        $items = [];
        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $items[$itemData['item_key']] = new CartItemEntity(
                    $itemData['product_id'],
                    $itemData['product_name'],
                    Money::from($itemData['price']),
                    Quantity::from($itemData['quantity']),
                    $itemData['size'] ?? null,
                    $itemData['color'] ?? null,
                    $itemData['image'] ?? null
                );
            }
        }

        return new CartEntity($cartKey, $items);
    }

    public function save(CartEntity $cart, int $ttlSeconds = 2592000): bool
    {
        return Cache::put(self::PREFIX . $cart->getCartKey(), $cart->toArray(), $ttlSeconds);
    }

    public function delete(string $cartKey): bool
    {
        return Cache::forget(self::PREFIX . $cartKey);
    }
}