<?php

namespace Modules\Catalog\Domain\Events;

use Shared\Domain\Contracts\DomainEventInterface;

class ProductCreatedEvent implements DomainEventInterface
{
    private string $occurredAt;

    public function __construct(
        private int|string $productId,
        private string $productName
    ) {
        $this->occurredAt = date('c');
    }

    public function getEventName(): string
    {
        return 'catalog.product.created';
    }

    public function getOccurredAt(): string
    {
        return $this->occurredAt;
    }

    public function toPayload(): array
    {
        return [
            'product_id' => $this->productId,
            'product_name' => $this->productName,
        ];
    }
}