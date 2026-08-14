<?php

namespace App\Http\Controllers;

use App\Models\SleepAssessment;
use App\Models\SleepEntry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SleepAssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SleepAssessment::where('user_id', Auth::id())
            ->orderBy('assessment_date', 'desc');

        // Optional date range filtering
        if ($request->has('start_date')) {
            $query->where('assessment_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('assessment_date', '<=', $request->end_date);
        }

        $assessments = $query->with('sleepEntry')->get();

        return response()->json([
            'success' => true,
            'data' => $assessments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sleep_entry_id' => 'nullable|exists:sleep_entries,id',
            'assessment_date' => 'required|date',
            'subjective_sleep_quality' => 'nullable|integer|min:0|max:3',
            'sleep_latency_score' => 'nullable|integer|min:0|max:3',
            'sleep_duration_score' => 'nullable|integer|min:0|max:3',
            'sleep_efficiency_score' => 'nullable|integer|min:0|max:3',
            'sleep_disturbances_score' => 'nullable|integer|min:0|max:3',
            'sleep_medication_score' => 'nullable|integer|min:0|max:3',
            'daytime_dysfunction_score' => 'nullable|integer|min:0|max:3',
            'overall_sleep_rating' => 'nullable|integer|min:1|max:10',
            'energy_level_morning' => 'nullable|integer|min:1|max:10',
            'energy_level_evening' => 'nullable|integer|min:1|max:10',
            'used_sleep_aids' => 'nullable|boolean',
            'consumed_caffeine' => 'nullable|boolean',
            'consumed_alcohol' => 'nullable|boolean',
            'exercise_minutes' => 'nullable|integer|min:0',
            'stress_level' => 'nullable|integer|min:1|max:10',
            'additional_notes' => 'nullable|string|max:2000',
        ]);

        $validated['user_id'] = Auth::id();

        // Auto-calculate PSQI score if component scores provided
        $assessment = new SleepAssessment($validated);
        if ($this->hasPsqiComponents($validated)) {
            $validated['total_psqi_score'] = $assessment->calculateTotalPsqiScore();
            $validated['sleep_quality_category'] = $assessment->sleep_quality_category;
        }

        $created = SleepAssessment::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sleep assessment created successfully',
            'data' => $created->load('sleepEntry'),
            'analysis' => $created->analysis,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SleepAssessment $sleepAssessment): JsonResponse
    {
        // Ensure user owns this assessment
        if ($sleepAssessment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $sleepAssessment->load('sleepEntry'),
            'analysis' => $sleepAssessment->analysis,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SleepAssessment $sleepAssessment): JsonResponse
    {
        // Ensure user owns this assessment
        if ($sleepAssessment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'sleep_entry_id' => 'nullable|exists:sleep_entries,id',
            'assessment_date' => 'sometimes|required|date',
            'subjective_sleep_quality' => 'nullable|integer|min:0|max:3',
            'sleep_latency_score' => 'nullable|integer|min:0|max:3',
            'sleep_duration_score' => 'nullable|integer|min:0|max:3',
            'sleep_efficiency_score' => 'nullable|integer|min:0|max:3',
            'sleep_disturbances_score' => 'nullable|integer|min:0|max:3',
            'sleep_medication_score' => 'nullable|integer|min:0|max:3',
            'daytime_dysfunction_score' => 'nullable|integer|min:0|max:3',
            'overall_sleep_rating' => 'nullable|integer|min:1|max:10',
            'energy_level_morning' => 'nullable|integer|min:1|max:10',
            'energy_level_evening' => 'nullable|integer|min:1|max:10',
            'used_sleep_aids' => 'nullable|boolean',
            'consumed_caffeine' => 'nullable|boolean',
            'consumed_alcohol' => 'nullable|boolean',
            'exercise_minutes' => 'nullable|integer|min:0',
            'stress_level' => 'nullable|integer|min:1|max:10',
            'additional_notes' => 'nullable|string|max:2000',
        ]);

        // Recalculate PSQI score if components changed
        if ($this->hasPsqiComponents($validated, true)) {
            $sleepAssessment->fill($validated);
            $validated['total_psqi_score'] = $sleepAssessment->calculateTotalPsqiScore();
            $validated['sleep_quality_category'] = $sleepAssessment->sleep_quality_category;
        }

        $sleepAssessment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sleep assessment updated successfully',
            'data' => $sleepAssessment->fresh(['sleepEntry']),
            'analysis' => $sleepAssessment->fresh()->analysis,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SleepAssessment $sleepAssessment): JsonResponse
    {
        // Ensure user owns this assessment
        if ($sleepAssessment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $sleepAssessment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sleep assessment deleted successfully',
        ]);
    }

    /**
     * Check if PSQI component scores are present
     */
    private function hasPsqiComponents(array $data, bool $allowPartial = false): bool
    {
        $components = [
            'subjective_sleep_quality',
            'sleep_latency_score',
            'sleep_duration_score',
            'sleep_efficiency_score',
            'sleep_disturbances_score',
            'sleep_medication_score',
            'daytime_dysfunction_score',
        ];

        if ($allowPartial) {
            // Check if any component is present
            foreach ($components as $component) {
                if (array_key_exists($component, $data)) {
                    return true;
                }
            }
            return false;
        }

        // Check if all components are present
        foreach ($components as $component) {
            if (!isset($data[$component])) {
                return false;
            }
        }
        return true;
    }
}
