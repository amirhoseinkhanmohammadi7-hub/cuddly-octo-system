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
        Schema::create('sleep_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sleep_entry_id')->nullable()->constrained()->onDelete('set null');
            $table->date('assessment_date');
            
            // PSQI (Pittsburgh Sleep Quality Index) components
            $table->integer('subjective_sleep_quality')->nullable(); // 0-3 scale
            $table->integer('sleep_latency_score')->nullable(); // 0-3 scale
            $table->integer('sleep_duration_score')->nullable(); // 0-3 scale
            $table->integer('sleep_efficiency_score')->nullable(); // 0-3 scale
            $table->integer('sleep_disturbances_score')->nullable(); // 0-3 scale
            $table->integer('sleep_medication_score')->nullable(); // 0-3 scale
            $table->integer('daytime_dysfunction_score')->nullable(); // 0-3 scale
            
            $table->integer('total_psqi_score')->nullable(); // 0-21 scale
            $table->string('sleep_quality_category')->nullable(); // Good/Poor sleeper
            
            // Additional assessment metrics
            $table->integer('overall_sleep_rating')->nullable(); // 1-10 scale
            $table->integer('energy_level_morning')->nullable(); // 1-10 scale
            $table->integer('energy_level_evening')->nullable(); // 1-10 scale
            $table->boolean('used_sleep_aids')->default(false);
            $table->boolean('consumed_caffeine')->default(false);
            $table->boolean('consumed_alcohol')->default(false);
            $table->integer('exercise_minutes')->default(0);
            $table->integer('stress_level')->nullable(); // 1-10 scale
            $table->text('additional_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'assessment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sleep_assessments');
    }
};
