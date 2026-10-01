<?php

namespace Shared\Domain\Contracts;

interface EntityInterface
{
    /**
     * Get the unique identifier of the entity.
     *
     * @return int|string|null
     */
    public function getId(): int|string|null;
}