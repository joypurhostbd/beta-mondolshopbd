<?php

namespace Modules\Catalog\Domain\Events;

use Shared\Domain\Contracts\DomainEventInterface;

class ProductUpdatedEvent implements DomainEventInterface
{
    private string $occurredAt;

    public function __construct(
        public readonly int|string $productId,
        public readonly array $updatedAttributes = []
    ) {
        $this->occurredAt = date('c');
    }

    public function getEventName(): string
    {
        return 'catalog.product.updated';
    }

    public function getOccurredAt(): string
    {
        return $this->occurredAt;
    }

    public function toPayload(): array
    {
        return [
            'product_id' => $this->productId,
            'updated_attributes' => $this->updatedAttributes,
        ];
    }
}