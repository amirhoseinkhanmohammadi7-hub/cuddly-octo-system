<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepAssessment extends Model
{
    protected $fillable = [
        'user_id',
        'sleep_entry_id',
        'assessment_date',
        'subjective_sleep_quality',
        'sleep_latency_score',
        'sleep_duration_score',
        'sleep_efficiency_score',
        'sleep_disturbances_score',
        'sleep_medication_score',
        'daytime_dysfunction_score',
        'total_psqi_score',
        'sleep_quality_category',
        'overall_sleep_rating',
        'energy_level_morning',
        'energy_level_evening',
        'used_sleep_aids',
        'consumed_caffeine',
        'consumed_alcohol',
        'exercise_minutes',
        'stress_level',
        'additional_notes',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'used_sleep_aids' => 'boolean',
        'consumed_caffeine' => 'boolean',
        'consumed_alcohol' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sleepEntry(): BelongsTo
    {
        return $this->belongsTo(SleepEntry::class);
    }

    /**
     * Calculate total PSQI score from component scores
     */
    public function calculateTotalPsqiScore(): int
    {
        $scores = [
            $this->subjective_sleep_quality ?? 0,
            $this->sleep_latency_score ?? 0,
            $this->sleep_duration_score ?? 0,
            $this->sleep_efficiency_score ?? 0,
            $this->sleep_disturbances_score ?? 0,
            $this->sleep_medication_score ?? 0,
            $this->daytime_dysfunction_score ?? 0,
        ];

        return array_sum($scores);
    }

    /**
     * Determine sleep quality category based on PSQI score
     * PSQI > 5 indicates poor sleep quality
     */
    public function getSleepQualityCategoryAttribute(): string
    {
        $score = $this->total_psqi_score ?? $this->calculateTotalPsqiScore();
        
        if ($score <= 5) {
            return 'Good Sleeper';
        } else {
            return 'Poor Sleeper';
        }
    }

    /**
     * Get comprehensive sleep analysis
     */
    public function getAnalysisAttribute(): array
    {
        return [
            'psqi_score' => $this->total_psqi_score ?? $this->calculateTotalPsqiScore(),
            'category' => $this->sleep_quality_category,
            'recommendations' => $this->getRecommendations(),
        ];
    }

    /**
     * Generate personalized recommendations based on assessment
     */
    private function getRecommendations(): array
    {
        $recommendations = [];

        if (($this->subjective_sleep_quality ?? 0) >= 2) {
            $recommendations[] = 'Consider improving your sleep environment (dark, quiet, cool room).';
        }

        if (($this->sleep_latency_score ?? 0) >= 2) {
            $recommendations[] = 'Try relaxation techniques before bed (deep breathing, meditation).';
        }

        if (($this->sleep_duration_score ?? 0) >= 2) {
            $recommendations[] = 'Aim for 7-9 hours of sleep per night.';
        }

        if (($this->daytime_dysfunction_score ?? 0) >= 2) {
            $recommendations[] = 'Maintain a consistent sleep schedule, even on weekends.';
        }

        if ($this->consumed_caffeine) {
            $recommendations[] = 'Avoid caffeine at least 6 hours before bedtime.';
        }

        if ($this->consumed_alcohol) {
            $recommendations[] = 'Limit alcohol consumption, especially in the evening.';
        }

        if ($this->exercise_minutes < 30) {
            $recommendations[] = 'Increase daily physical activity to improve sleep quality.';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Great job! Continue maintaining your healthy sleep habits.';
        }

        return $recommendations;
    }
}
