<?php

namespace Shared\Domain\Enums;

enum ProductStatusEnum: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;
    case DRAFT = 2;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::DRAFT => 'Draft',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
            self::DRAFT => 'secondary',
        };
    }
}