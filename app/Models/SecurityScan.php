<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SecurityScan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'scan_type',
        'status',
        'health_score',
        'total_checks',
        'passed_checks',
        'warning_checks',
        'critical_checks',
        'results',
        'scanned_by',
    ];

    protected $casts = [
        'results' => 'array',
        'health_score' => 'integer',
        'total_checks' => 'integer',
        'passed_checks' => 'integer',
        'warning_checks' => 'integer',
        'critical_checks' => 'integer',
    ];

    public function getScoreBadgeClassAttribute(): string
    {
        if ($this->health_score >= 85) {
            return 'badge-success';
        }
        if ($this->health_score >= 65) {
            return 'badge-warning';
        }
        return 'badge-danger';
    }
}

