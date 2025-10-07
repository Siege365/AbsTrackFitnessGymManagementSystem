<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    protected $fillable = [
        'name',
        'age',
        'avatar',
        'plan_type',
        'start_date',
        'due_date',
        'status',
        'contact',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];
}
