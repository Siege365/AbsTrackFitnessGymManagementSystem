<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'module',
        'description',
        'reference_number',
        'customer_name',
        'subject_type',
        'subject_id',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Log an activity.
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        ?string $referenceNumber = null,
        ?string $customerName = null,
        $subject = null,
        array $metadata = []
    ): self {
        $user = Auth::user();

        return static::create([
            'user_id'          => $user?->id,
            'user_name'        => $user?->name ?? 'System',
            'action'           => $action,
            'module'           => $module,
            'description'      => $description,
            'reference_number' => $referenceNumber,
            'customer_name'    => $customerName,
            'subject_type'     => $subject ? get_class($subject) : null,
            'subject_id'       => $subject?->id ?? null,
            'metadata'         => !empty($metadata) ? $metadata : null,
            'ip_address'       => request()->ip(),
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
