<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Credited = 'credited';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refund = 'refund';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Credited => 'Credited',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
            self::Refund => 'Refund',
        };
    }
}
