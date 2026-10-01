<?php

namespace Shared\Domain\Exceptions;

class InvalidStateTransitionException extends DomainException
{
    public static function forTransition(string $entity, string $fromState, string $toState): self
    {
        return new self(
            "Cannot transition [{$entity}] from state [{$fromState}] to [{$toState}].",
            ['entity' => $entity, 'from_state' => $fromState, 'to_state' => $toState],
            422
        );
    }
}