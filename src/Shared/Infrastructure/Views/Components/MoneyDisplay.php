<?php

namespace Shared\Infrastructure\Views\Components;

use Illuminate\View\Component;
use Shared\Domain\ValueObjects\Money;

class MoneyDisplay extends Component
{
    public function __construct(
        public Money|float|int|string $amount,
        public string $currency = '৳'
    ) {}

    public function formatted(): string
    {
        if ($this->amount instanceof Money) {
            return $this->amount->format();
        }
        return Money::from((float) $this->amount)->format();
    }

    public function render()
    {
        return view('components.shared.money-display');
    }
}