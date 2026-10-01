<?php

namespace Modules\Customer\Domain\Contracts;

use Modules\Customer\Domain\Entities\CustomerEntity;
use Shared\Domain\Contracts\EntityInterface;
use Shared\Domain\Contracts\RepositoryInterface;

interface CustomerRepositoryInterface extends RepositoryInterface
{
    public function findById(int|string $id): ?CustomerEntity;

    public function findByPhone(string $phone): ?CustomerEntity;

    public function findByEmail(string $email): ?CustomerEntity;
}