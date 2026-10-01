<?php

namespace Shared\Domain\Contracts\Modules;

interface CustomerModuleInterface
{
    /**
     * Find a customer by ID.
     *
     * @param int|string $customerId
     * @return array|null
     */
    public function findCustomerById(int|string $customerId): ?array;

    /**
     * Find a customer by phone number.
     *
     * @param string $phone
     * @return array|null
     */
    public function findCustomerByPhone(string $phone): ?array;
}