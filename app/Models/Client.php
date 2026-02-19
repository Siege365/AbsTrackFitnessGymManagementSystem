<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Client extends Model
{
    protected $fillable = [
        'name',
        'age',
        'sex',
        'avatar',
        'plan_type',
        'start_date',
        'due_date',
        'contact',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Always append the status accessor to array/JSON output
     */
    protected $appends = ['status'];

    /**
     * Get the gym plan associated with this client
     */
    public function gymPlan()
    {
        return $this->belongsTo(GymPlan::class, 'plan_type', 'plan_key');
    }

    /**
     * Get the status attribute - automatically calculated based on due_date
     * This ensures status is always accurate and real-time
     * 
     * @return string
     */
    public function getStatusAttribute()
    {
        // If there's a stored status value, we'll override it with the calculated one
        // This accessor runs on every model load, ensuring real-time accuracy
        
        if (!$this->due_date) {
            return 'Active'; // Default if no due date
        }

        $today = Carbon::today();
        $dueDate = Carbon::parse($this->due_date);
        
        // Check if expired (past due date)
        if ($dueDate->lt($today)) {
            return 'Expired';
        }
        
        // Check if due soon (within 7 days)
        $sevenDaysFromNow = $today->copy()->addDays(7);
        if ($dueDate->lte($sevenDaysFromNow)) {
            return 'Due soon';
        }
        
        // Otherwise, it's active
        return 'Active';
    }
}
