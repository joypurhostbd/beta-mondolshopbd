<?php

namespace Shared\Domain\Contracts;

interface DomainEventInterface
{
    /**
     * Get the name of the domain event.
     *
     * @return string
     */
    public function getEventName(): string;

    /**
     * Get the event payload as an array.
     *
     * @return array
     */
    public function toPayload(): array;

    /**
     * Get the timestamp when the event occurred.
     *
     * @return string
     */
    public function getOccurredAt(): string;
}