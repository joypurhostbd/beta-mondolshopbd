<?php

namespace Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Shared\Domain\Contracts\ValueObjectInterface;

final class Quantity implements ValueObjectInterface
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Quantity must be a positive integer greater than zero. Given: {$value}");
        }

        $this->value = $value;
    }

    public static function from(int $value): self
    {
        return new self($value);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function add(Quantity $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(Quantity $other): self
    {
        if ($this->value <= $other->value) {
            throw new InvalidArgumentException("Cannot subtract {$other->value} from {$this->value} (result must be > 0).");
        }
        return new self($this->value - $other->value);
    }

    public function multiply(int $multiplier): self
    {
        if ($multiplier <= 0) {
            throw new InvalidArgumentException("Multiplier must be greater than zero.");
        }
        return new self($this->value * $multiplier);
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}