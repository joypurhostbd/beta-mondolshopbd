<?php

namespace Modules\Promotion\Domain\Entities;

class ReviewEntity
{
    public function __construct(
        public readonly ?int $id,
        public int $productId,
        public ?int $customerId,
        public string $customerName,
        public ?string $customerEmail,
        public int $rating,
        public string $reviewText,
        public string $status = 'pending' // pending, active, rejected
    ) {}
}