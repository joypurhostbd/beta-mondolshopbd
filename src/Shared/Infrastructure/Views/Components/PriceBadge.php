<?php

namespace Shared\Infrastructure\Views\Components;

use Illuminate\View\Component;
use Shared\Domain\ValueObjects\Money;

class PriceBadge extends Component
{
    public function __construct(
        public Money $price,
        public ?Money $oldPrice = null,
        public bool $showDiscount = true
    ) {}

    public function isDiscounted(): bool
    {
        return $this->oldPrice !== null && $this->oldPrice->isGreaterThan($this->price);
    }

    public function discountPercentage(): int
    {
        if (!$this->isDiscounted() || $this->oldPrice->getAmount() <= 0) {
            return 0;
        }
        $diff = $this->oldPrice->subtract($this->price);
        return (int) round(($diff->getAmount() / $this->oldPrice->getAmount()) * 100);
    }

    public function render()
    {
        return view('components.shared.price-badge');
    }
}