<?php

namespace Modules\Inventory\Domain\Entities;

use Shared\Domain\Contracts\EntityInterface;
use Shared\Domain\ValueObjects\Quantity;

class InventoryLedgerEntity implements EntityInterface
{
    public function __construct(
        private int|string $id,
        private int|string $productId,
        private Quantity $quantity,
        private string $type, // 'credit', 'debit', 'reservation'
        private ?string $reference = null,
        private ?string $notes = null
    ) {}

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getProductId(): int|string
    {
        return $this->productId;
    }

    public function getQuantity(): Quantity
    {
        return $this->quantity;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'quantity' => $this->quantity->getValue(),
            'type' => $this->type,
            'reference' => $this->reference,
            'notes' => $this->notes,
        ];
    }
}