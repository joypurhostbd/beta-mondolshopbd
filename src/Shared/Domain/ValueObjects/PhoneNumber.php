<?php

namespace Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Shared\Domain\Contracts\ValueObjectInterface;

final class PhoneNumber implements ValueObjectInterface
{
    private string $number;

    public function __construct(string $rawPhone)
    {
        // Remove spaces, dashes, parentheses
        $cleaned = preg_replace('/[\s\-\(\)]+/', '', trim($rawPhone));

        // Match Bangladeshi mobile numbers: optional +88 or 88, then 01[3-9]XXXXXXXX (11 digits)
        if (preg_match('/^(?:\+88|88)?(01[3-9]\d{8})$/', $cleaned, $matches)) {
            $this->number = $matches[1];
        } else {
            throw new InvalidArgumentException("Invalid Bangladeshi phone number: {$rawPhone}");
        }
    }

    public static function from(string $rawPhone): self
    {
        return new self($rawPhone);
    }

    public static function fromString(string $rawPhone): self
    {
        return new self($rawPhone);
    }

    public function getValue(): string
    {
        return $this->number;
    }

    public function getInternational(): string
    {
        return '+88' . $this->number;
    }

    public function toE164(): string
    {
        return '+88' . $this->number;
    }

    public function getFormatted(): string
    {
        // Format as 017XX-XXXXXX
        return substr($this->number, 0, 5) . '-' . substr($this->number, 5);
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->number === $other->number;
    }

    public function __toString(): string
    {
        return $this->number;
    }
}