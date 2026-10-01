<?php

namespace Modules\Customer\Application\DTOs;

use Modules\Customer\Domain\Entities\CustomerEntity;
use Shared\Application\DTO\DataTransferObject;

class CustomerDTO extends DataTransferObject
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name,
        public readonly string $phone,
        public readonly ?string $email = null,
        public readonly ?string $address = null,
        public readonly ?string $district = null,
        public readonly ?string $area = null,
        public readonly string $status = 'active',
        public readonly float $balance = 0.0,
        public readonly bool $isVerified = true
    ) {}

    public static function fromEntity(CustomerEntity $entity): self
    {
        return new self(
            id: $entity->getId(),
            name: $entity->getName(),
            phone: $entity->getPhone()->getValue(),
            email: $entity->getEmail()?->getValue(),
            address: $entity->getAddress(),
            district: $entity->getDistrict(),
            area: $entity->getArea(),
            status: $entity->getStatus()->value,
            balance: $entity->getBalance()->getAmount(),
            isVerified: $entity->isVerified()
        );
    }
}