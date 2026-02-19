<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GymPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'plan_name',
        'plan_key',
        'price',
        'duration_days',
        'duration_label',
        'badge_text',
        'badge_color',
        'requires_student',
        'requires_buddy',
        'buddy_count',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'requires_student' => 'boolean',
        'requires_buddy' => 'boolean',
        'buddy_count' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /* ───── Scopes ───── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMembership($query)
    {
        return $query->where('category', 'membership');
    }

    public function scopePersonalTraining($query)
    {
        return $query->where('category', 'personal_training');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('plan_name');
    }

    /* ───── Helpers ───── */

    /**
     * Get the per-person price (used for buddy plans).
     */
    public function getPerPersonPriceAttribute(): float
    {
        return $this->buddy_count > 1
            ? round($this->price / $this->buddy_count, 2)
            : $this->price;
    }

    /**
     * Formatted price with ₱ symbol.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₱' . number_format($this->price, 2);
    }
}
