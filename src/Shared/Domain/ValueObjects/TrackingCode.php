<?php

namespace Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Shared\Domain\Contracts\ValueObjectInterface;

final class TrackingCode implements ValueObjectInterface
{
    private string $code;

    public function __construct(string $rawCode)
    {
        $cleaned = strtoupper(trim($rawCode));

        if (empty($cleaned) || strlen($cleaned) < 3) {
            throw new InvalidArgumentException("Tracking code must be at least 3 characters. Given: {$rawCode}");
        }

        $this->code = $cleaned;
    }

    public static function from(string $rawCode): self
    {
        return new self($rawCode);
    }

    public function getValue(): string
    {
        return $this->code;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->code === $other->code;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}