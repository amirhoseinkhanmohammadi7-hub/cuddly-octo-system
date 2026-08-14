<?php

namespace App\Http\Controllers;

use App\Models\SleepEntry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SleepEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SleepEntry::where('user_id', Auth::id())
            ->orderBy('sleep_date', 'desc');

        // Optional date range filtering
        if ($request->has('start_date')) {
            $query->where('sleep_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('sleep_date', '<=', $request->end_date);
        }

        $entries = $query->with('assessment')->get();

        return response()->json([
            'success' => true,
            'data' => $entries,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sleep_date' => 'required|date',
            'bedtime' => 'nullable|date_format:H:i',
            'wake_time' => 'nullable|date_format:H:i',
            'sleep_duration_minutes' => 'nullable|integer|min:0',
            'time_to_fall_asleep_minutes' => 'nullable|integer|min:0',
            'night_awakenings' => 'nullable|integer|min:0',
            'sleep_notes' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = Auth::id();

        // Auto-calculate sleep duration if not provided
        if (!isset($validated['sleep_duration_minutes']) && isset($validated['bedtime']) && isset($validated['wake_time'])) {
            $entry = new SleepEntry($validated);
            $validated['sleep_duration_minutes'] = $entry->calculateSleepDuration();
        }

        $entry = SleepEntry::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sleep entry created successfully',
            'data' => $entry->load('assessment'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SleepEntry $sleepEntry): JsonResponse
    {
        // Ensure user owns this entry
        if ($sleepEntry->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $sleepEntry->load('assessment'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SleepEntry $sleepEntry): JsonResponse
    {
        // Ensure user owns this entry
        if ($sleepEntry->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'sleep_date' => 'sometimes|required|date',
            'bedtime' => 'nullable|date_format:H:i',
            'wake_time' => 'nullable|date_format:H:i',
            'sleep_duration_minutes' => 'nullable|integer|min:0',
            'time_to_fall_asleep_minutes' => 'nullable|integer|min:0',
            'night_awakenings' => 'nullable|integer|min:0',
            'sleep_notes' => 'nullable|string|max:1000',
        ]);

        // Auto-calculate sleep duration if times changed
        if (isset($validated['bedtime']) || isset($validated['wake_time'])) {
            $sleepEntry->fill($validated);
            if (!isset($validated['sleep_duration_minutes'])) {
                $validated['sleep_duration_minutes'] = $sleepEntry->calculateSleepDuration();
            }
        }

        $sleepEntry->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sleep entry updated successfully',
            'data' => $sleepEntry->fresh(['assessment']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SleepEntry $sleepEntry): JsonResponse
    {
        // Ensure user owns this entry
        if ($sleepEntry->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $sleepEntry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sleep entry deleted successfully',
        ]);
    }
}
