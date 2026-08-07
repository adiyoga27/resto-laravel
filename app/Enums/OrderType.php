<?php

namespace App\Enums;

enum OrderType: string
{
    case DineIn = 'dine-in';
    case Delivery = 'delivery';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::DineIn => 'Dine In',
            self::Delivery => 'Delivery',
            self::Pickup => 'Pickup',
        };
    }
}
