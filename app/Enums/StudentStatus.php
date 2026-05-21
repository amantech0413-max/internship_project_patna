<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Completed = 'completed';
}
