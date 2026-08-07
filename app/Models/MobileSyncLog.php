<?php

namespace App\Models;

use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileSyncLog extends Model
{
    use HasFactory;

    #[Fillable(['user_id', 'device_id', 'idempotency_key', 'action_type', 'payload', 'status', 'error_message', 'synced_at'])]
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'status' => SyncStatus::class,
            'synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
