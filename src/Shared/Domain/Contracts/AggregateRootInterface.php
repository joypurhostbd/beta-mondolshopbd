<?php

namespace Shared\Domain\Contracts;

interface AggregateRootInterface extends EntityInterface
{
    /**
     * Pull and clear recorded domain events.
     *
     * @return array<DomainEventInterface>
     */
    public function releaseEvents(): array;
}