<?php

namespace App\Models;

use Database\Factories\StoreProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreProfile extends Model
{
    /** @use HasFactory<StoreProfileFactory> */
    use HasFactory;

    protected $fillable = ['store_name', 'store_address', 'store_phone', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('store_name')->orderByDesc('id');
    }
}
