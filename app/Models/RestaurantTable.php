<?php

namespace App\Models;

use App\Enums\TableStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantTable extends Model
{
    use HasFactory;

    protected $fillable = ['table_number', 'capacity', 'status'];

    protected function casts(): array
    {
        return [
            'status' => TableStatus::class,
            'capacity' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'restaurant_table_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'restaurant_table_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === TableStatus::Kosong;
    }
}
