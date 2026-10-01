<?php

namespace Modules\Promotion\Infrastructure\Repositories;

use App\Models\Review;
use Modules\Promotion\Domain\Contracts\ReviewRepositoryInterface;
use Modules\Promotion\Domain\Entities\ReviewEntity;
use Throwable;

class EloquentReviewRepository implements ReviewRepositoryInterface
{
    public function findByProduct(int $productId): array
    {
        try {
            return Review::where('product_id', $productId)
                ->where('status', 'active')
                ->get()
                ->map(fn($r) => $this->toEntity($r))
                ->toArray();
        } catch (Throwable) {
            return [];
        }
    }

    public function save(ReviewEntity $entity): ReviewEntity
    {
        $r = $entity->id ? Review::find($entity->id) : new Review();
        if (!$r) {
            $r = new Review();
        }

        $r->product_id = $entity->productId;
        $r->customer_id = $entity->customerId;
        $r->name = $entity->customerName;
        $r->email = $entity->customerEmail ?? 'customer@example.com';
        $r->rating = $entity->rating;
        $r->review = $entity->reviewText;
        $r->status = $entity->status;
        $r->save();

        return $this->toEntity($r);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $r = Review::find($id);
        if ($r) {
            $r->status = $status;
            return (bool) $r->save();
        }
        return false;
    }

    private function toEntity(Review $r): ReviewEntity
    {
        return new ReviewEntity(
            id: $r->id,
            productId: (int) $r->product_id,
            customerId: $r->customer_id ? (int) $r->customer_id : null,
            customerName: $r->name ?? 'Customer',
            customerEmail: $r->email,
            rating: (int) ($r->rating ?? $r->ratting ?? 5),
            reviewText: $r->review ?? '',
            status: (string) ($r->status ?? 'pending')
        );
    }
}