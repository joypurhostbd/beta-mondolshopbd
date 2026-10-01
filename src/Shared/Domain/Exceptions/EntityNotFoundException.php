<?php

namespace Shared\Domain\Exceptions;

class EntityNotFoundException extends DomainException
{
    public static function forEntity(string $entity, int|string $id): self
    {
        return new self(
            "Entity [{$entity}] with identifier [{$id}] was not found.",
            ['entity' => $entity, 'id' => $id],
            404
        );
    }
}