<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'module',
        'description',
        'reference_number',
        'customer_name',
        'subject_id',
        'subject_type',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the subject model (polymorphic).
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ──

    public function scopeModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    /**
     * Helper: create a log entry from anywhere.
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        ?string $referenceNumber = null,
        ?string $customerName = null,
        $subject = null,
        ?array $metadata = null
    ): self {
        $user = auth()->user();

        return self::create([
            'user_id'          => $user?->id,
            'user_name'        => $user?->name ?? 'System',
            'action'           => $action,
            'module'           => $module,
            'description'      => $description,
            'reference_number' => $referenceNumber,
            'customer_name'    => $customerName,
            'subject_id'       => $subject?->id ?? null,
            'subject_type'     => $subject ? get_class($subject) : null,
            'metadata'         => $metadata,
            'ip_address'       => request()->ip(),
        ]);
    }
}
