<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityAiConsultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'topic',
        'query',
        'ai_response',
        'suggested_actions',
    ];

    protected $casts = [
        'suggested_actions' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

