<?php

namespace Modules\Catalog\Domain\Events;

use Shared\Domain\Contracts\DomainEventInterface;

class ProductStockUpdatedEvent implements DomainEventInterface
{
    private string $occurredAt;

    public function __construct(
        private int|string $productId,
        private int $oldStock,
        private int $newStock
    ) {
        $this->occurredAt = date('c');
    }

    public function getEventName(): string
    {
        return 'catalog.product.stock_updated';
    }

    public function getOccurredAt(): string
    {
        return $this->occurredAt;
    }

    public function toPayload(): array
    {
        return [
            'product_id' => $this->productId,
            'old_stock' => $this->oldStock,
            'new_stock' => $this->newStock,
        ];
    }
}