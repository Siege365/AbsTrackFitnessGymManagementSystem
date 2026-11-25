<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SecurityThreatAlert extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'threat_type',
        'severity',
        'title',
        'description',
        'source_ip',
        'affected_user',
        'metadata',
        'status',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function getSeverityBadgeClassAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'badge-danger',
            'high'     => 'badge-danger',
            'medium'   => 'badge-warning',
            'low'      => 'badge-info',
            default    => 'badge-secondary',
        };
    }
}

