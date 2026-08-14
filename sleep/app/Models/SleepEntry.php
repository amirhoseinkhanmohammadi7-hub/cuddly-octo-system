<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SleepEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'sleep_date',
        'bedtime',
        'wake_time',
        'sleep_duration_minutes',
        'time_to_fall_asleep_minutes',
        'night_awakenings',
        'sleep_quality_score',
        'notes',
        'factors',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sleep_date' => 'date',
        'bedtime' => 'datetime:H:i',
        'wake_time' => 'datetime:H:i',
        'factors' => 'array',
    ];

    /**
     * Get the user that owns the sleep entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate sleep efficiency score (0-100)
     */
    public function getSleepEfficiencyAttribute(): ?float
    {
        if ($this->sleep_duration_minutes <= 0) {
            return null;
        }
        
        $timeInBed = $this->sleep_duration_minutes + 
                     ($this->time_to_fall_asleep_minutes ?? 0) +
                     (($this->night_awakenings ?? 0) * 15); // assume 15 min per awakening
        
        if ($timeInBed <= 0) {
            return null;
        }
        
        return round(($this->sleep_duration_minutes / $timeInBed) * 100, 2);
    }

    /**
     * Get sleep quality category
     */
    public function getQualityCategoryAttribute(): string
    {
        $score = $this->sleep_quality_score ?? 5;
        
        if ($score >= 8) {
            return 'Excellent';
        } elseif ($score >= 6) {
            return 'Good';
        } elseif ($score >= 4) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }
}
