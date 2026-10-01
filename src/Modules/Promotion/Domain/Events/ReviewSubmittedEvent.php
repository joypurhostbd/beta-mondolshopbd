<?php

namespace Modules\Promotion\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewSubmittedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $reviewId,
        public readonly int $productId,
        public readonly int $rating
    ) {}
}