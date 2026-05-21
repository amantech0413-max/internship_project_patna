<?php

namespace App\Enums;

enum WhatsappMessageStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
}
