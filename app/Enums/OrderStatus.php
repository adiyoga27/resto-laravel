<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Baru = 'baru';
    case Diproses = 'diproses';
    case Siap = 'siap';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Diproses => 'Diproses',
            self::Siap => 'Siap',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public static function kitchenStatuses(): array
    {
        return [self::Baru, self::Diproses, self::Siap];
    }
}
