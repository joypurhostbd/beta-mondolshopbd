<?php

namespace App\ValueObjects;

readonly class Phone
{
    private const BANGLA_DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    private const ENGLISH_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    public string $value;

    public function __construct(string $value)
    {
        $normalized = self::normalize($value);
        if (!self::isValid($normalized)) {
            throw new \InvalidArgumentException("Invalid Bangladeshi phone: {$value}");
        }
        $this->value = $normalized;
    }

    /**
     * Normalize phone input: convert Bangla digits to English, strip formatting,
     * and strip leading +88 / 88 prefixes.
     */
    public static function normalize(?string $phone): string
    {
        if ($phone === null) {
            return '';
        }

        // Convert Bengali digits to English
        $phone = str_replace(self::BANGLA_DIGITS, self::ENGLISH_DIGITS, trim($phone));

        // Remove any characters other than digits
        $phone = preg_replace('/[^\d]/', '', $phone);

        // Strip leading 880 if present and total length is 13 (e.g. 88017XXXXXXXX)
        if (str_starts_with($phone, '8801') && strlen($phone) === 13) {
            $phone = substr($phone, 2);
        }

        return $phone;
    }

    /**
     * Check whether phone number matches standard 11-digit Bangladeshi mobile format.
     */
    public static function isValid(?string $phone): bool
    {
        if (empty($phone)) {
            return false;
        }

        return (bool) preg_match('/^01[3-9]\d{8}$/', self::normalize($phone));
    }

    public function formatted(): string
    {
        return $this->value;
    }

    /**
     * Convert any phone input to international WhatsApp format (e.g. 8801XXXXXXXXX).
     */
    public static function toWhatsApp(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $normalized = self::normalize($phone);

        // Standard 11-digit Bangladeshi mobile starting with 01 (e.g. 01972101994)
        if (str_starts_with($normalized, '01') && strlen($normalized) === 11) {
            return '88' . $normalized;
        }

        // 10-digit number without leading 0 (e.g. 1972101994)
        if (str_starts_with($normalized, '1') && strlen($normalized) === 10) {
            return '880' . $normalized;
        }

        // Already formatted 13-digit number (e.g. 8801972101994)
        if (str_starts_with($normalized, '8801') && strlen($normalized) === 13) {
            return $normalized;
        }

        return $normalized;
    }

    public function forWhatsApp(): string
    {
        return self::toWhatsApp($this->value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}