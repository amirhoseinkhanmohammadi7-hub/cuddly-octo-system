<?php

namespace App\Http\Controllers;

use App\Models\SleepEntry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SleepEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SleepEntry::where('user_id', Auth::id())
            ->orderBy('sleep_date', 'desc');

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('sleep_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('sleep_date', '<=', $request->end_date);
        }

        $entries = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $entries
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sleep_date' => 'required|date',
            'bedtime' => 'required|date_format:H:i',
            'wake_time' => 'required|date_format:H:i',
            'sleep_duration_minutes' => 'required|integer|min:0|max:1440',
            'time_to_fall_asleep_minutes' => 'nullable|integer|min:0',
            'night_awakenings' => 'nullable|integer|min:0',
            'sleep_quality_score' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string|max:1000',
            'factors' => 'nullable|array',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['time_to_fall_asleep_minutes'] = $validated['time_to_fall_asleep_minutes'] ?? 0;
        $validated['night_awakenings'] = $validated['night_awakenings'] ?? 0;

        $entry = SleepEntry::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sleep entry created successfully',
            'data' => $entry
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SleepEntry $entry): JsonResponse
    {
        if ($entry->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $entry
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SleepEntry $entry): JsonResponse
    {
        if ($entry->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'sleep_date' => 'sometimes|required|date',
            'bedtime' => 'sometimes|required|date_format:H:i',
            'wake_time' => 'sometimes|required|date_format:H:i',
            'sleep_duration_minutes' => 'sometimes|required|integer|min:0|max:1440',
            'time_to_fall_asleep_minutes' => 'nullable|integer|min:0',
            'night_awakenings' => 'nullable|integer|min:0',
            'sleep_quality_score' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string|max:1000',
            'factors' => 'nullable|array',
        ]);

        $entry->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sleep entry updated successfully',
            'data' => $entry->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SleepEntry $entry): JsonResponse
    {
        if ($entry->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $entry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sleep entry deleted successfully'
        ]);
    }

    /**
     * Get sleep statistics for the user
     */
    public function statistics(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $entries = SleepEntry::where('user_id', Auth::id())
            ->whereBetween('sleep_date', [$startDate, $endDate])
            ->get();

        if ($entries->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'average_sleep_duration' => 0,
                    'average_quality_score' => 0,
                    'total_entries' => 0,
                    'sleep_efficiency' => 0,
                    'trend' => 'stable'
                ]
            ]);
        }

        $avgDuration = $entries->avg('sleep_duration_minutes');
        $avgQuality = $entries->avg('sleep_quality_score') ?? 0;
        $avgEfficiency = $entries->filter(function($e) {
            return $e->sleep_efficiency !== null;
        })->avg(function($e) {
            return $e->sleep_efficiency;
        }) ?? 0;

        // Calculate trend (compare last 7 days to previous 7 days)
        $last7Days = SleepEntry::where('user_id', Auth::id())
            ->whereBetween('sleep_date', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::now()->format('Y-m-d')])
            ->avg('sleep_quality_score');
        
        $previous7Days = SleepEntry::where('user_id', Auth::id())
            ->whereBetween('sleep_date', [Carbon::now()->subDays(14)->format('Y-m-d'), Carbon::now()->subDays(7)->format('Y-m-d')])
            ->avg('sleep_quality_score');

        $trend = 'stable';
        if ($last7Days > $previous7Days + 0.5) {
            $trend = 'improving';
        } elseif ($last7Days < $previous7Days - 0.5) {
            $trend = 'declining';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'average_sleep_duration' => round($avgDuration, 2),
                'average_quality_score' => round($avgQuality, 2),
                'total_entries' => $entries->count(),
                'sleep_efficiency' => round($avgEfficiency, 2),
                'trend' => $trend,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]
        ]);
    }
}
