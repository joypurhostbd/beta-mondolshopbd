<?php

namespace Modules\Promotion\Application\DTOs;

class ReviewDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $productId,
        public readonly ?int $customerId,
        public readonly string $customerName,
        public readonly ?string $customerEmail,
        public readonly int $rating,
        public readonly string $reviewText,
        public readonly string $status = 'pending'
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'customer_id' => $this->customerId,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'rating' => $this->rating,
            'review_text' => $this->reviewText,
            'status' => $this->status,
        ];
    }
}