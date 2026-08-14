<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sleep_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('sleep_date');
            $table->time('bedtime');
            $table->time('wake_time');
            $table->integer('sleep_duration_minutes');
            $table->integer('time_to_fall_asleep_minutes')->default(0);
            $table->integer('night_awakenings')->default(0);
            $table->integer('sleep_quality_score')->nullable(); // 1-10 scale
            $table->text('notes')->nullable();
            $table->json('factors')->nullable(); // caffeine, exercise, stress, etc.
            $table->timestamps();
            
            $table->index(['user_id', 'sleep_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sleep_entries');
    }
};
