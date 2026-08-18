<?php

namespace App\Models;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'customer_id', 'restaurant_table_id', 'created_by',
        'channel', 'order_type', 'order_status', 'is_posted_to_cash_flow',
        'subtotal', 'discount', 'tax', 'total',
        'notes', 'delivery_address', 'customer_name', 'customer_phone',
    ];

    protected function casts(): array
    {
        return [
            'channel' => OrderChannel::class,
            'order_type' => OrderType::class,
            'order_status' => OrderStatus::class,
            'is_posted_to_cash_flow' => 'boolean',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
            }
        });
    }

    public function restaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeKitchenQueue($query)
    {
        return $query->whereIn('order_status', ['baru', 'diproses', 'siap']);
    }

    public function scopeForKasir($query, $kasirId)
    {
        return $query->where('created_by', $kasirId);
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->orderItems->sum('subtotal');
        $this->tax = $this->subtotal * 0.11;
        $this->total = $this->subtotal + $this->tax;
        $this->save();
    }

    public function mutateStock(): void
    {
        $this->load('orderItems.menuItem.recipeItems.ingredient');

        foreach ($this->orderItems as $item) {
            $menuItem = $item->menuItem;
            if (! $menuItem) {
                continue;
            }

            foreach ($menuItem->recipeItems as $recipe) {
                $ingredient = $recipe->ingredient;
                $totalUsed = $recipe->quantity * $item->quantity;
                $stockBefore = $ingredient->current_stock;
                $stockAfter = $stockBefore - $totalUsed;

                StockLog::create([
                    'ingredient_id' => $ingredient->id,
                    'menu_item_id' => $menuItem->id,
                    'type' => 'out',
                    'quantity' => $totalUsed,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference' => 'Order #'.$this->order_number,
                    'notes' => 'Penjualan: '.$menuItem->name.' x'.$item->quantity,
                    'user_id' => auth()->id() ?? $this->created_by ?? 1,
                ]);

                $ingredient->update(['current_stock' => $stockAfter]);
            }
        }
    }
}
