<?php

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shared\Domain\ValueObjects\Money;

class SharedDomainContractsTest extends TestCase
{
    public function test_money_value_object_initialization_and_formatting(): void
    {
        $money = Money::from(150.50, 'BDT');

        $this->assertEquals(150.50, $money->getAmount());
        $this->assertEquals('BDT', $money->getCurrency());
        $this->assertEquals('৳150.50', $money->format());
    }

    public function test_money_addition_and_subtraction(): void
    {
        $m1 = Money::from(100);
        $m2 = Money::from(50);

        $sum = $m1->add($m2);
        $this->assertEquals(150.00, $sum->getAmount());

        $diff = $m1->subtract($m2);
        $this->assertEquals(50.00, $diff->getAmount());
    }

    public function test_money_equality(): void
    {
        $m1 = Money::from(200, 'BDT');
        $m2 = Money::from(200.00, 'BDT');
        $m3 = Money::from(300, 'BDT');

        $this->assertTrue($m1->equals($m2));
        $this->assertFalse($m1->equals($m3));
    }

    public function test_negative_money_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::from(-10);
    }
}