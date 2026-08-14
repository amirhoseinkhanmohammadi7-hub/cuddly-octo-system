<?php

namespace Database\Factories;

use App\Models\SleepEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepEntry>
 */
class SleepEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sleepDuration = fake()->numberBetween(360, 540); // 6-9 hours in minutes
        $bedtimeHour = fake()->numberBetween(20, 23);
        $bedtimeMinute = fake()->numberBetween(0, 59);
        
        return [
            'sleep_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'bedtime' => sprintf('%02d:%02d', $bedtimeHour, $bedtimeMinute),
            'wake_time' => sprintf('%02d:%02d', ($bedtimeHour + intdiv($sleepDuration, 60)) % 24, ($bedtimeMinute + $sleepDuration % 60) % 60),
            'sleep_duration_minutes' => $sleepDuration,
            'time_to_fall_asleep_minutes' => fake()->numberBetween(5, 45),
            'night_awakenings' => fake()->numberBetween(0, 3),
            'sleep_quality_score' => fake()->numberBetween(3, 10),
            'notes' => fake()->optional(0.3)->sentence(),
            'factors' => fake()->randomElement([
                ['caffeine', 'exercise'],
                ['stress'],
                ['exercise', 'relaxation'],
                [],
                ['caffeine', 'late_meal'],
            ]),
        ];
    }
}
