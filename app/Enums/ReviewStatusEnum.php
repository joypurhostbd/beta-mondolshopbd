<?php

namespace App\Enums;

enum ReviewStatusEnum: string
{
    case Pending = 'pending';
    case Approved = 'active';
    case Rejected = 'inactive';
}