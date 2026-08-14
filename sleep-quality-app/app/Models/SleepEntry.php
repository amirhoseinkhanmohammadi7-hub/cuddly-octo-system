<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SleepEntry extends Model
{
    protected $fillable = [
        'user_id',
        'sleep_date',
        'bedtime',
        'wake_time',
        'sleep_duration_minutes',
        'time_to_fall_asleep_minutes',
        'night_awakenings',
        'sleep_notes',
    ];

    protected $casts = [
        'sleep_date' => 'date',
        'bedtime' => 'datetime:H:i',
        'wake_time' => 'datetime:H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(SleepAssessment::class);
    }

    /**
     * Calculate sleep duration in minutes if not set
     */
    public function calculateSleepDuration(): ?int
    {
        if ($this->bedtime && $this->wake_time) {
            $bedtime = \Carbon\Carbon::parse($this->bedtime);
            $wakeTime = \Carbon\Carbon::parse($this->wake_time);
            
            // Handle crossing midnight
            if ($wakeTime < $bedtime) {
                $wakeTime->addDay();
            }
            
            return $bedtime->diffInMinutes($wakeTime);
        }
        
        return null;
    }

    /**
     * Get sleep quality label based on duration
     */
    public function getSleepQualityLabelAttribute(): string
    {
        $duration = $this->sleep_duration_minutes ?? $this->calculateSleepDuration();
        
        if (!$duration) {
            return 'Unknown';
        }
        
        if ($duration < 360) { // Less than 6 hours
            return 'Poor';
        } elseif ($duration < 420) { // 6-7 hours
            return 'Fair';
        } elseif ($duration < 540) { // 7-9 hours
            return 'Good';
        } else { // More than 9 hours
            return 'Excellent';
        }
    }
}
