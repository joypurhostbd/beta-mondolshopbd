<?php

namespace Tests\Unit\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shared\Domain\ValueObjects\Email;

class EmailTest extends TestCase
{
    public function test_can_be_created_from_valid_email(): void
    {
        $email = Email::from('test@example.com');
        $this->assertSame('test@example.com', $email->getValue());
        $this->assertSame('test@example.com', (string) $email);
    }

    public function test_normalizes_and_trims_email(): void
    {
        $email = Email::from('  USER.NAME@Example.COM  ');
        $this->assertSame('user.name@example.com', $email->getValue());
    }

    public function test_extracts_domain_correctly(): void
    {
        $email = Email::from('admin@joypurhost.com');
        $this->assertSame('joypurhost.com', $email->getDomain());
    }

    public function test_throws_exception_on_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Email::from('invalid-email-address');
    }

    public function test_equality_comparison(): void
    {
        $email1 = Email::from('contact@joypurhost.com');
        $email2 = Email::from('CONTACT@joypurhost.com');
        $email3 = Email::from('other@joypurhost.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }
}
