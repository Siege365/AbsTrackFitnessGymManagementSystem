<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    protected function logActivity(string $action, string $description, $subject = null, array $properties = [], string $module = 'general'): ActivityLog
    {
        $user = auth()->user();

        return ActivityLog::create([
            'user_id'      => $user?->id,
            'user_name'    => $user?->name ?? 'System',
            'action'       => $action,
            'module'       => $module,
            'description'  => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->id,
            'ip_address'   => request()->ip(),
            'metadata'     => !empty($properties) ? $properties : null,
        ]);
    }
}
