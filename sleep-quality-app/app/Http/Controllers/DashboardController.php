<?php

namespace App\Http\Controllers;

use App\Models\SleepEntry;
use App\Models\SleepAssessment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display dashboard statistics and insights
     */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();
        
        // Get date range (default to last 30 days)
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days)->format('Y-m-d');
        
        // Sleep statistics
        $sleepStats = $this->getSleepStatistics($userId, $startDate);
        
        // Assessment statistics
        $assessmentStats = $this->getAssessmentStatistics($userId, $startDate);
        
        // Recent entries
        $recentEntries = SleepEntry::where('user_id', $userId)
            ->where('sleep_date', '>=', $startDate)
            ->orderBy('sleep_date', 'desc')
            ->limit(5)
            ->with('assessment')
            ->get();
        
        // Weekly trend data
        $weeklyTrend = $this->getWeeklyTrendData($userId, $startDate);
        
        // Sleep quality distribution
        $qualityDistribution = $this->getSleepQualityDistribution($userId, $startDate);
        
        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'days' => $days,
                    'start_date' => $startDate,
                    'end_date' => now()->format('Y-m-d'),
                ],
                'sleep_statistics' => $sleepStats,
                'assessment_statistics' => $assessmentStats,
                'recent_entries' => $recentEntries,
                'weekly_trend' => $weeklyTrend,
                'quality_distribution' => $qualityDistribution,
            ],
        ]);
    }

    /**
     * Get sleep entry statistics
     */
    private function getSleepStatistics(int $userId, string $startDate): array
    {
        $stats = SleepEntry::where('user_id', $userId)
            ->where('sleep_date', '>=', $startDate)
            ->select(
                DB::raw('AVG(sleep_duration_minutes) as avg_duration'),
                DB::raw('MIN(sleep_duration_minutes) as min_duration'),
                DB::raw('MAX(sleep_duration_minutes) as max_duration'),
                DB::raw('AVG(time_to_fall_asleep_minutes) as avg_sleep_latency'),
                DB::raw('SUM(night_awakenings) as total_awakenings'),
                DB::raw('COUNT(*) as total_entries')
            )
            ->first();

        return [
            'average_sleep_duration_minutes' => round($stats->avg_duration ?? 0, 2),
            'min_sleep_duration_minutes' => $stats->min_duration ?? 0,
            'max_sleep_duration_minutes' => $stats->max_duration ?? 0,
            'average_sleep_latency_minutes' => round($stats->avg_sleep_latency ?? 0, 2),
            'total_night_awakenings' => $stats->total_awakenings ?? 0,
            'total_entries' => $stats->total_entries ?? 0,
            'average_sleep_hours' => round(($stats->avg_duration ?? 0) / 60, 2),
        ];
    }

    /**
     * Get assessment statistics
     */
    private function getAssessmentStatistics(int $userId, string $startDate): array
    {
        $stats = SleepAssessment::where('user_id', $userId)
            ->where('assessment_date', '>=', $startDate)
            ->select(
                DB::raw('AVG(total_psqi_score) as avg_psqi_score'),
                DB::raw('AVG(overall_sleep_rating) as avg_rating'),
                DB::raw('AVG(energy_level_morning) as avg_energy_morning'),
                DB::raw('AVG(energy_level_evening) as avg_energy_evening'),
                DB::raw('AVG(stress_level) as avg_stress'),
                DB::raw('COUNT(*) as total_assessments')
            )
            ->first();

        // Count good vs poor sleepers
        $goodSleepers = SleepAssessment::where('user_id', $userId)
            ->where('assessment_date', '>=', $startDate)
            ->where(function ($query) {
                $query->where('total_psqi_score', '<=', 5)
                      ->orWhereRaw("
                          COALESCE(subjective_sleep_quality, 0) + 
                          COALESCE(sleep_latency_score, 0) + 
                          COALESCE(sleep_duration_score, 0) + 
                          COALESCE(sleep_efficiency_score, 0) + 
                          COALESCE(sleep_disturbances_score, 0) + 
                          COALESCE(sleep_medication_score, 0) + 
                          COALESCE(daytime_dysfunction_score, 0) <= 5
                      ");
            })
            ->count();

        $totalAssessments = $stats->total_assessments ?? 0;
        $poorSleepers = $totalAssessments - $goodSleepers;

        return [
            'average_psqi_score' => round($stats->avg_psqi_score ?? 0, 2),
            'average_sleep_rating' => round($stats->avg_rating ?? 0, 2),
            'average_energy_morning' => round($stats->avg_energy_morning ?? 0, 2),
            'average_energy_evening' => round($stats->avg_energy_evening ?? 0, 2),
            'average_stress_level' => round($stats->avg_stress ?? 0, 2),
            'total_assessments' => $totalAssessments,
            'good_sleeper_days' => $goodSleepers,
            'poor_sleeper_days' => $poorSleepers,
        ];
    }

    /**
     * Get weekly trend data for charts
     */
    private function getWeeklyTrendData(int $userId, string $startDate): array
    {
        $trendData = SleepEntry::where('user_id', $userId)
            ->where('sleep_date', '>=', $startDate)
            ->select(
                'sleep_date',
                'sleep_duration_minutes',
                'time_to_fall_asleep_minutes'
            )
            ->orderBy('sleep_date', 'asc')
            ->get()
            ->map(function ($entry) {
                return [
                    'date' => $entry->sleep_date->format('Y-m-d'),
                    'duration_minutes' => $entry->sleep_duration_minutes,
                    'sleep_latency_minutes' => $entry->time_to_fall_asleep_minutes,
                ];
            });

        return $trendData->toArray();
    }

    /**
     * Get sleep quality distribution
     */
    private function getSleepQualityDistribution(int $userId, string $startDate): array
    {
        $entries = SleepEntry::where('user_id', $userId)
            ->where('sleep_date', '>=', $startDate)
            ->whereNotNull('sleep_duration_minutes')
            ->get();

        $distribution = [
            'excellent' => 0, // > 9 hours
            'good' => 0,      // 7-9 hours
            'fair' => 0,      // 6-7 hours
            'poor' => 0,      // < 6 hours
        ];

        foreach ($entries as $entry) {
            $duration = $entry->sleep_duration_minutes;
            
            if ($duration >= 540) {
                $distribution['excellent']++;
            } elseif ($duration >= 420) {
                $distribution['good']++;
            } elseif ($duration >= 360) {
                $distribution['fair']++;
            } else {
                $distribution['poor']++;
            }
        }

        return $distribution;
    }
}
