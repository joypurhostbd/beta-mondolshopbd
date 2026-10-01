<?php

namespace Shared\Domain\Contracts;

interface ValueObjectInterface
{
    /**
     * Determine if two value objects are equal in value.
     *
     * @param ValueObjectInterface $other
     * @return bool
     */
    public function equals(ValueObjectInterface $other): bool;
}