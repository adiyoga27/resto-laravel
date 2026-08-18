<?php

namespace App\Enums;

enum CashFlowType: string
{
    case Debit = 'debit';
    case Kredit = 'kredit';

    public function label(): string
    {
        return match ($this) {
            self::Debit => 'Debit (Masuk)',
            self::Kredit => 'Kredit (Keluar)',
        };
    }
}
