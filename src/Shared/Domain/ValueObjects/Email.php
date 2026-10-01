<?php

namespace Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Shared\Domain\Contracts\ValueObjectInterface;

final class Email implements ValueObjectInterface
{
    private string $email;

    public function __construct(string $rawEmail)
    {
        $cleaned = strtolower(trim($rawEmail));

        if (!filter_var($cleaned, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$rawEmail}");
        }

        $this->email = $cleaned;
    }

    public static function from(string $rawEmail): self
    {
        return new self($rawEmail);
    }

    public function getValue(): string
    {
        return $this->email;
    }

    public function getDomain(): string
    {
        $parts = explode('@', $this->email);
        return $parts[1] ?? '';
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->email === $other->email;
    }

    public function __toString(): string
    {
        return $this->email;
    }
}