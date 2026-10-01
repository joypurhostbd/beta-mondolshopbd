<?php

namespace Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Shared\Domain\Contracts\ValueObjectInterface;

final class Money implements ValueObjectInterface
{
    private float $amount;
    private string $currency;

    public function __construct(float|int|string $amount, string $currency = 'BDT')
    {
        $parsedAmount = (float) $amount;
        if ($parsedAmount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }

        $this->amount = round($parsedAmount, 2);
        $this->currency = strtoupper(trim($currency));
    }

    public static function from(float|int|string $amount, string $currency = 'BDT'): self
    {
        return new self($amount, $currency);
    }

    public static function zero(string $currency = 'BDT'): self
    {
        return new self(0, $currency);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function isZero(): bool
    {
        return abs($this->amount) < 0.0001;
    }

    public function isPositive(): bool
    {
        return $this->amount > 0.0001;
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return ($this->amount - $other->amount) > 0.0001;
    }

    public function isLessThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return ($other->amount - $this->amount) > 0.0001;
    }

    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);
        if ($this->amount < $other->amount) {
            throw new InvalidArgumentException('Resulting money amount cannot be negative.');
        }
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float|int $multiplier): self
    {
        if ($multiplier < 0) {
            throw new InvalidArgumentException('Multiplier cannot be negative.');
        }
        return new self($this->amount * $multiplier, $this->currency);
    }

    public function format(): string
    {
        return ($this->currency === 'BDT' ? '৳' : $this->currency . ' ') . number_format($this->amount, 2);
    }

    public function equals(ValueObjectInterface $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->currency === $other->currency && abs($this->amount - $other->amount) < 0.0001;
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Currency mismatch: {$this->currency} vs {$other->currency}");
        }
    }
}