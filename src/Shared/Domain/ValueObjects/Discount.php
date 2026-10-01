<?php

namespace Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Shared\Domain\Contracts\ValueObjectInterface;

final class Discount implements ValueObjectInterface
{
    private ?float $percentage;
    private ?Money $fixedAmount;

    private function __construct(?float $percentage, ?Money $fixedAmount)
    {
        $this->percentage = $percentage;
        $this->fixedAmount = $fixedAmount;
    }

    public static function fromPercentage(float|int $percentage): self
    {
        $val = (float) $percentage;
        if ($val < 0 || $val > 100) {
            throw new InvalidArgumentException("Discount percentage must be between 0 and 100. Given: {$percentage}");
        }
        return new self($val, null);
    }

    public static function percentage(float|int $percentage): self
    {
        return self::fromPercentage($percentage);
    }

    public static function fromFixed(Money $fixedAmount): self
    {
        return new self(null, $fixedAmount);
    }

    public static function fixed(float|int|Money $fixedAmount): self
    {
        $money = $fixedAmount instanceof Money ? $fixedAmount : Money::from((float) $fixedAmount);
        return new self(null, $money);
    }

    public function isPercentage(): bool
    {
        return $this->percentage !== null;
    }

    public function getPercentage(): ?float
    {
        return $this->percentage;
    }

    public function getFixedAmount(): ?Money
    {
        return $this->fixedAmount;
    }

    public function calculate(Money $subtotal): Money
    {
        if ($this->isPercentage()) {
            $discountAmount = $subtotal->getAmount() * ($this->percentage / 100);
            return Money::from(min($discountAmount, $subtotal->getAmount()), $subtotal->getCurrency());
        }

        return Money::from(min($this->fixedAmount->getAmount(), $subtotal->getAmount()), $subtotal->getCurrency());
    }

    public function equals(ValueObjectInterface $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        if ($this->isPercentage() !== $other->isPercentage()) {
            return false;
        }

        if ($this->isPercentage()) {
            return abs($this->percentage - $other->percentage) < 0.0001;
        }

        return $this->fixedAmount->equals($other->fixedAmount);
    }
}