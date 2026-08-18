<?php

namespace App\Models;

use App\Enums\CashFlowType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashFlow extends Model
{
    protected $fillable = ['date', 'description', 'type', 'amount', 'reference', 'is_posted', 'posted_at', 'posted_by', 'created_by'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'type' => CashFlowType::class,
            'amount' => 'decimal:2',
            'is_posted' => 'boolean',
            'posted_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }

    public function scopeDraft($query)
    {
        return $query->where('is_posted', false);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    public function post(?int $userId): void
    {
        $this->update([
            'is_posted' => true,
            'posted_at' => now(),
            'posted_by' => $userId,
        ]);
    }

    public function unpost(): void
    {
        $this->update([
            'is_posted' => false,
            'posted_at' => null,
            'posted_by' => null,
        ]);
    }
}
