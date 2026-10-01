<?php

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shared\Domain\ValueObjects\PhoneNumber;
use Shared\Domain\ValueObjects\Email;
use Shared\Domain\ValueObjects\Quantity;
use Shared\Domain\ValueObjects\Discount;
use Shared\Domain\ValueObjects\TrackingCode;
use Shared\Domain\ValueObjects\Money;

class CoreValueObjectsTest extends TestCase
{
    public function test_phone_number_bangladeshi_formats(): void
    {
        $p1 = PhoneNumber::from('01712345678');
        $p2 = PhoneNumber::from('+8801712345678');
        $p3 = PhoneNumber::from('8801712345678');
        $p4 = PhoneNumber::from('01712-345678');

        $this->assertEquals('01712345678', $p1->getValue());
        $this->assertTrue($p1->equals($p2));
        $this->assertTrue($p1->equals($p3));
        $this->assertTrue($p1->equals($p4));
        $this->assertEquals('+8801712345678', $p1->getInternational());
        $this->assertEquals('01712-345678', $p1->getFormatted());
    }

    public function test_invalid_phone_number_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PhoneNumber::from('0123456789'); // 012 is invalid BD prefix
    }

    public function test_email_validation_and_normalization(): void
    {
        $e1 = Email::from('USER@example.COM ');
        $this->assertEquals('user@example.com', $e1->getValue());
        $this->assertEquals('example.com', $e1->getDomain());
    }

    public function test_quantity_operations(): void
    {
        $q1 = Quantity::from(5);
        $q2 = Quantity::from(3);

        $this->assertEquals(8, $q1->add($q2)->getValue());
        $this->assertEquals(2, $q1->subtract($q2)->getValue());
        $this->assertEquals(15, $q1->multiply(3)->getValue());
    }

    public function test_discount_calculation(): void
    {
        $subtotal = Money::from(1000, 'BDT');

        $percentDiscount = Discount::fromPercentage(15);
        $calcPercent = $percentDiscount->calculate($subtotal);
        $this->assertEquals(150.00, $calcPercent->getAmount());

        $fixedDiscount = Discount::fromFixed(Money::from(200, 'BDT'));
        $calcFixed = $fixedDiscount->calculate($subtotal);
        $this->assertEquals(200.00, $calcFixed->getAmount());
    }

    public function test_tracking_code_value_object(): void
    {
        $t = TrackingCode::from(' inv-100234 ');
        $this->assertEquals('INV-100234', $t->getValue());
    }
}