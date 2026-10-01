<?php

namespace Modules\Customer\Domain\Contracts;

interface SmsServiceInterface
{
    /**
     * Send a general text message to a recipient phone number.
     *
     * @param string $recipientPhone
     * @param string $message
     * @return bool
     */
    public function send(string $recipientPhone, string $message): bool;

    /**
     * Send a standard OTP verification SMS.
     *
     * @param string $recipientPhone
     * @param int $otp
     * @return bool
     */
    public function sendOtp(string $recipientPhone, int $otp): bool;
}