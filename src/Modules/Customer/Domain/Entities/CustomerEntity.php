<?php

namespace Modules\Customer\Domain\Entities;

use Shared\Domain\Contracts\EntityInterface;
use Shared\Domain\Enums\CustomerStatusEnum;
use Shared\Domain\ValueObjects\Email;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;

class CustomerEntity implements EntityInterface
{
    public function __construct(
        private int|string $id,
        private string $name,
        private PhoneNumber $phone,
        private ?Email $email = null,
        private ?string $address = null,
        private ?string $district = null,
        private ?string $area = null,
        private CustomerStatusEnum $status = CustomerStatusEnum::ACTIVE,
        private Money $balance = new Money(0),
        private ?int $verify = 1
    ) {}

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): PhoneNumber
    {
        return $this->phone;
    }

    public function getEmail(): ?Email
    {
        return $this->email;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function getStatus(): CustomerStatusEnum
    {
        return $this->status;
    }

    public function getBalance(): Money
    {
        return $this->balance;
    }

    public function isVerified(): bool
    {
        return $this->verify === 1;
    }

    public function isActive(): bool
    {
        return $this->status === CustomerStatusEnum::ACTIVE;
    }
}