<?php

namespace App\Enums;

enum SyncStatus: string
{
    case Pending = 'pending';
    case Synced = 'synced';
    case Conflict = 'conflict';
    case Failed = 'failed';
}
