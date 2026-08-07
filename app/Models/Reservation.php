<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    #[Fillable(['restaurant_table_id', 'customer_id', 'reservation_time', 'guest_count', 'status', 'notes'])]
    protected function casts(): array
    {
        return [
            'reservation_time' => 'datetime',
            'guest_count' => 'integer',
            'status' => ReservationStatus::class,
        ];
    }

    public function restaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
