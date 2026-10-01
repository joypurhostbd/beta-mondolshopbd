<?php

namespace Modules\Order\Domain\Entities;

use Shared\Domain\Contracts\AggregateRootInterface;
use Shared\Domain\Contracts\DomainEventInterface;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\Quantity;

class CartEntity implements AggregateRootInterface
{
    /**
     * @var array<DomainEventInterface>
     */
    private array $events = [];

    /**
     * @param array<string, CartItemEntity> $items
     */
    public function __construct(
        private string $cartKey,
        private array $items = []
    ) {}

    public function recordEvent(DomainEventInterface $event): void
    {
        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }

    public function getId(): string
    {
        return $this->cartKey;
    }

    public function getCartKey(): string
    {
        return $this->cartKey;
    }

    /**
     * @return array<string, CartItemEntity>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(CartItemEntity $item): void
    {
        $key = $item->getItemKey();
        if (isset($this->items[$key])) {
            $existing = $this->items[$key];
            $newQty = $existing->getQuantity()->add($item->getQuantity());
            $this->items[$key] = $existing->withQuantity($newQty);
        } else {
            $this->items[$key] = $item;
        }
    }

    public function updateItemQuantity(string $itemKey, Quantity $quantity): void
    {
        if (isset($this->items[$itemKey])) {
            $this->items[$itemKey] = $this->items[$itemKey]->withQuantity($quantity);
        }
    }

    public function removeItem(string $itemKey): void
    {
        unset($this->items[$itemKey]);
    }

    public function clear(): void
    {
        $this->items = [];
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function getTotal(): Money
    {
        $total = Money::zero();
        foreach ($this->items as $item) {
            $total = $total->add($item->getSubtotal());
        }
        return $total;
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }

    public function getTotalQuantity(): int
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getQuantity()->getValue();
        }
        return $total;
    }

    public function toArray(): array
    {
        $itemsArray = [];
        foreach ($this->items as $key => $item) {
            $itemsArray[$key] = $item->toArray();
        }

        return [
            'cart_key' => $this->cartKey,
            'items' => $itemsArray,
            'item_count' => $this->getItemCount(),
            'total_quantity' => $this->getTotalQuantity(),
            'total' => $this->getTotal()->getAmount(),
            'total_formatted' => $this->getTotal()->format(),
        ];
    }
}