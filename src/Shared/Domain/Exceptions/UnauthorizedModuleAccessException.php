<?php

namespace Shared\Domain\Exceptions;

class UnauthorizedModuleAccessException extends DomainException
{
    public static function forModule(string $module, ?string $user = null): self
    {
        return new self(
            "Access to module [{$module}] is unauthorized for user [{$user}].",
            ['module' => $module, 'user' => $user],
            403
        );
    }
}