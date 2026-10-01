<?php

namespace Shared\Domain\Contracts;

interface RepositoryInterface
{
    /**
     * Find an entity by its identifier.
     *
     * @param int|string $id
     * @return EntityInterface|null
     */
    public function findById(int|string $id): ?EntityInterface;

    /**
     * Save an entity to persistence.
     *
     * @param EntityInterface $entity
     * @return EntityInterface
     */
    public function save(EntityInterface $entity): EntityInterface;

    /**
     * Delete an entity by its identifier.
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteById(int|string $id): bool;
}