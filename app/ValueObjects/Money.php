<?php

namespace App\ValueObjects;

readonly class Money
{
    public function __construct(
        public float $amount,
        public string $currency = 'BDT',
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function fromDecimal(float $amount): self
    {
        return new self(round($amount, 2));
    }

    public function add(self $other): self
    {
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function formatted(): string
    {
        return '৳' . number_format($this->amount, 2);
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }
}