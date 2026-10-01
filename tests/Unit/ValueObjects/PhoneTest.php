<?php

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\Phone;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public function test_normalizes_bengali_digits_to_english(): void
    {
        $this->assertSame('01972101994', Phone::normalize('০১৯৭২১০১৯৯৪'));
        $this->assertSame('01712345678', Phone::normalize('০১৭১২৩৪৫৬৭৮'));
    }

    public function test_strips_international_prefixes_and_formatting(): void
    {
        $this->assertSame('01972101994', Phone::normalize('+8801972101994'));
        $this->assertSame('01972101994', Phone::normalize('8801972101994'));
        $this->assertSame('01972101994', Phone::normalize('01972-101 994'));
        $this->assertSame('01972101994', Phone::normalize('+৮৮০১৯৭২-১০১ ৯৯৪'));
    }

    public function test_validates_bangladeshi_mobile_numbers(): void
    {
        $this->assertTrue(Phone::isValid('01972101994'));
        $this->assertTrue(Phone::isValid('০১৯৭২১০১৯৯৪'));
        $this->assertTrue(Phone::isValid('+8801712345678'));

        // Invalid numbers
        $this->assertFalse(Phone::isValid('01212345678')); // invalid operator code
        $this->assertFalse(Phone::isValid('019721019')); // too short
        $this->assertFalse(Phone::isValid('0197210199499')); // too long
        $this->assertFalse(Phone::isValid(''));
        $this->assertFalse(Phone::isValid(null));
    }

    public function test_instantiates_phone_value_object_with_bengali_digits(): void
    {
        $phone = new Phone('০১৯৭২১০১৯৯৪');
        $this->assertSame('01972101994', $phone->value);
        $this->assertSame('01972101994', $phone->formatted());
    }

    public function test_throws_exception_on_invalid_phone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Phone('123456');
    }

    public function test_formats_phone_numbers_for_whatsapp_preserving_880_country_code(): void
    {
        // Standard 11 digit mobile numbers (must produce 8801..., NOT 881...)
        $this->assertSame('8801972101994', Phone::toWhatsApp('01972101994'));
        $this->assertSame('8801712345678', Phone::toWhatsApp('01712345678'));

        // International format inputs with +880 or 880
        $this->assertSame('8801972101994', Phone::toWhatsApp('+8801972101994'));
        $this->assertSame('8801972101994', Phone::toWhatsApp('8801972101994'));

        // Formatted with spaces and dashes
        $this->assertSame('8801972101994', Phone::toWhatsApp('01972-101 994'));

        // Bengali digits
        $this->assertSame('8801972101994', Phone::toWhatsApp('০১৯৭২১০১৯৯৪'));

        // 10 digits without leading zero
        $this->assertSame('8801972101994', Phone::toWhatsApp('1972101994'));

        // Empty or null
        $this->assertSame('', Phone::toWhatsApp(''));
        $this->assertSame('', Phone::toWhatsApp(null));

        // Instance method
        $phone = new Phone('01972101994');
        $this->assertSame('8801972101994', $phone->forWhatsApp());
    }
}
