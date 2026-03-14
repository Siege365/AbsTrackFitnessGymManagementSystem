<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trainer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'specialization',
        'contact_number',
        'emergency_contact',
        'birth_date',
        'address',
        'avatar',
        'status',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function getDisplayIdAttribute(): string
    {
        return str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }
}
