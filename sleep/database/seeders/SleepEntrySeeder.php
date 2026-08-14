<?php

namespace Database\Seeders;

use App\Models\SleepEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

class SleepEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user or create one if none exists
        $user = User::first();
        
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Create 30 sleep entries for the user
        SleepEntry::factory()
            ->count(30)
            ->state(['user_id' => $user->id])
            ->create();
    }
}
