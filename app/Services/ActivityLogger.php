<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Catat aktivitas yang dilakukan oleh user.
     *
     * @param  array<string, mixed>  $data
     */
    public static function log(string $action, string $description, ?Model $subject = null, array $data = []): ActivityLog
    {
        $request = request();

        return ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 255) : null,
            'data' => $data ?: null,
        ]);
    }
}
