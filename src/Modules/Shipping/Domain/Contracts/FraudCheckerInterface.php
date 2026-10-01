<?php

namespace Modules\Shipping\Domain\Contracts;

use Modules\Shipping\Application\DTOs\FraudScoreDTO;
use Shared\Domain\ValueObjects\PhoneNumber;

interface FraudCheckerInterface
{
    public function checkCustomer(PhoneNumber $phoneNumber): FraudScoreDTO;
}