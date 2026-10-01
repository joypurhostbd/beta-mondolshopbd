<?php

namespace Modules\Customer\Application\Services;

use Modules\Customer\Application\DTOs\CustomerDTO;
use Modules\Customer\Domain\Contracts\CustomerRepositoryInterface;
use Shared\Domain\Contracts\Modules\CustomerModuleInterface;

class CustomerService implements CustomerModuleInterface
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function findCustomerById(int|string $customerId): ?array
    {
        $entity = $this->customerRepository->findById($customerId);
        if (!$entity) {
            return null;
        }

        return CustomerDTO::fromEntity($entity)->toArray();
    }

    public function findCustomerByPhone(string $phone): ?array
    {
        try {
            $entity = $this->customerRepository->findByPhone($phone);
            if (!$entity) {
                return null;
            }

            return CustomerDTO::fromEntity($entity)->toArray();
        } catch (\Throwable) {
            return null;
        }
    }
}