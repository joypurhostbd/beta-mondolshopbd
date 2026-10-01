<?php

namespace Modules\Promotion\Application\Actions;

use Modules\Promotion\Application\DTOs\ReviewDTO;
use Modules\Promotion\Domain\Contracts\ReviewRepositoryInterface;
use Modules\Promotion\Domain\Entities\ReviewEntity;
use Modules\Promotion\Domain\Events\ReviewSubmittedEvent;

class SubmitReviewAction
{
    public function __construct(
        private ReviewRepositoryInterface $repository
    ) {}

    public function execute(array $data): ReviewDTO
    {
        $entity = new ReviewEntity(
            id: null,
            productId: (int) $data['product_id'],
            customerId: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            customerName: $data['name'] ?? 'Customer',
            customerEmail: $data['email'] ?? null,
            rating: max(1, min(5, (int) ($data['ratting'] ?? $data['rating'] ?? 5))),
            reviewText: $data['review'] ?? $data['review_text'] ?? '',
            status: $data['status'] ?? 'pending'
        );

        $saved = $this->repository->save($entity);

        event(new ReviewSubmittedEvent($saved->id, $saved->productId, $saved->rating));

        return new ReviewDTO(
            id: $saved->id,
            productId: $saved->productId,
            customerId: $saved->customerId,
            customerName: $saved->customerName,
            customerEmail: $saved->customerEmail,
            rating: $saved->rating,
            reviewText: $saved->reviewText,
            status: $saved->status
        );
    }
}