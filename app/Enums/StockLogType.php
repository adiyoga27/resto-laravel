<?php

namespace App\Enums;

enum StockLogType: string
{
    case In = 'in';
    case Out = 'out';
    case Production = 'production';
    case Adjustment = 'adjustment';
    case Opname = 'opname';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Barang Masuk',
            self::Out => 'Barang Keluar',
            self::Production => 'Produksi',
            self::Adjustment => 'Penyesuaian',
            self::Opname => 'Stok Opname',
        };
    }
}
