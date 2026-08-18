<?php

namespace App\Models;

use App\Enums\StockLogType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends Model
{
    protected $fillable = ['ingredient_id', 'menu_item_id', 'type', 'quantity', 'stock_before', 'stock_after', 'reference', 'notes', 'user_id'];

    protected function casts(): array
    {
        return [
            'type' => StockLogType::class,
            'quantity' => 'decimal:2',
            'stock_before' => 'decimal:2',
            'stock_after' => 'decimal:2',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
