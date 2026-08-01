<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('status', 20)->default('active');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz()->index();
            $table->index(['status', 'name']);
        });

        DB::statement("ALTER TABLE routine_templates ADD CONSTRAINT routine_templates_name_not_blank CHECK (btrim(name) <> '')");
        DB::statement("ALTER TABLE routine_templates ADD CONSTRAINT routine_templates_status_valid CHECK (status IN ('active', 'archived'))");

        Schema::create('routine_template_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_template_id')->constrained('routine_templates')->restrictOnDelete();
            $table->foreignId('source_exercise_id')->nullable()->constrained('exercises')->nullOnDelete();
            $table->smallInteger('position');
            $table->string('name', 160);
            $table->text('description')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->smallInteger('sets')->nullable();
            $table->smallInteger('repetitions')->nullable();
            $table->text('material_url')->nullable();
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz();
        });

        DB::statement("ALTER TABLE routine_template_exercises ADD CONSTRAINT routine_template_exercises_name_not_blank CHECK (btrim(name) <> '')");
        DB::statement('ALTER TABLE routine_template_exercises ADD CONSTRAINT routine_template_exercises_position_positive CHECK (position > 0)');
        DB::statement('ALTER TABLE routine_template_exercises ADD CONSTRAINT routine_template_exercises_duration_positive CHECK (duration_seconds IS NULL OR duration_seconds > 0)');
        DB::statement('ALTER TABLE routine_template_exercises ADD CONSTRAINT routine_template_exercises_sets_positive CHECK (sets IS NULL OR sets > 0)');
        DB::statement('ALTER TABLE routine_template_exercises ADD CONSTRAINT routine_template_exercises_repetitions_positive CHECK (repetitions IS NULL OR repetitions > 0)');
        DB::statement('CREATE UNIQUE INDEX routine_template_exercises_position_unique ON routine_template_exercises (routine_template_id, position) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_template_exercises');
        Schema::dropIfExists('routine_templates');
    }
};
