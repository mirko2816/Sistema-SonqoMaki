<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->string('name', 160);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status', 20)->default('paused');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz()->index();
            $table->index('patient_id');
            $table->index(['patient_id', 'status']);
            $table->index(['starts_on', 'ends_on']);
        });
        DB::statement("ALTER TABLE plans ADD CONSTRAINT plans_name_not_blank CHECK (btrim(name) <> '')");
        DB::statement('ALTER TABLE plans ADD CONSTRAINT plans_dates_valid CHECK (starts_on <= ends_on)');
        DB::statement("ALTER TABLE plans ADD CONSTRAINT plans_status_valid CHECK (status IN ('active', 'paused', 'finished'))");

        Schema::create('routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->string('name', 160);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz()->index();
            $table->index(['plan_id', 'starts_on', 'ends_on']);
        });
        DB::statement("ALTER TABLE routines ADD CONSTRAINT routines_name_not_blank CHECK (btrim(name) <> '')");
        DB::statement('ALTER TABLE routines ADD CONSTRAINT routines_dates_valid CHECK (starts_on <= ends_on)');
        DB::statement("ALTER TABLE routines ADD CONSTRAINT routines_no_overlapping_dates EXCLUDE USING gist (plan_id WITH =, daterange(starts_on, ends_on, '[]') WITH &&) WHERE (deleted_at IS NULL)");

        Schema::create('routine_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained('routines')->restrictOnDelete();
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
            $table->softDeletesTz()->index();
            $table->index('routine_id');
        });
        DB::statement("ALTER TABLE routine_exercises ADD CONSTRAINT routine_exercises_name_not_blank CHECK (btrim(name) <> '')");
        DB::statement('ALTER TABLE routine_exercises ADD CONSTRAINT routine_exercises_position_positive CHECK (position > 0)');
        DB::statement('ALTER TABLE routine_exercises ADD CONSTRAINT routine_exercises_duration_positive CHECK (duration_seconds IS NULL OR duration_seconds > 0)');
        DB::statement('ALTER TABLE routine_exercises ADD CONSTRAINT routine_exercises_sets_positive CHECK (sets IS NULL OR sets > 0)');
        DB::statement('ALTER TABLE routine_exercises ADD CONSTRAINT routine_exercises_repetitions_positive CHECK (repetitions IS NULL OR repetitions > 0)');
        DB::statement('CREATE UNIQUE INDEX routine_exercises_position_unique ON routine_exercises (routine_id, position) WHERE deleted_at IS NULL');

        Schema::create('public_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->text('token_ciphertext');
            $table->string('token_prefix', 12);
            $table->timestampTz('revoked_at')->nullable()->index();
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
        });
        DB::statement("ALTER TABLE public_links ADD CONSTRAINT public_links_hash_format CHECK (token_hash ~ '^[0-9a-f]{64}$')");
        DB::statement("ALTER TABLE public_links ADD CONSTRAINT public_links_prefix_not_blank CHECK (btrim(token_prefix) <> '')");
        DB::statement('CREATE UNIQUE INDEX public_links_current_plan_unique ON public_links (plan_id) WHERE revoked_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('public_links');
        Schema::dropIfExists('routine_exercises');
        Schema::dropIfExists('routines');
        Schema::dropIfExists('plans');
    }
};
