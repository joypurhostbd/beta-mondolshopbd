<?php

namespace Shared\Domain\Enums;

enum AuditActionEnum: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case STATUS_CHANGE = 'status_change';

    public function label(): string
    {
        return match ($this) {
            self::CREATE => 'Created',
            self::UPDATE => 'Updated',
            self::DELETE => 'Deleted',
            self::LOGIN => 'Logged In',
            self::LOGOUT => 'Logged Out',
            self::STATUS_CHANGE => 'Status Changed',
        };
    }
}