<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(
        string $action,
        string $description,
        string $performedBy,
        string $entityType = '',
        ?string $entityId = null
    ): void {
        ActivityLog::create([
            'action'       => $action,
            'description'  => $description,
            'performed_by' => $performedBy,
            'entity_type'  => $entityType,
            'entity_id'    => $entityId,
        ]);
    }
}