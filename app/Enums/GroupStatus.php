<?php

namespace App\Enums;

enum GroupStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Inactive = 'inactive';
}
