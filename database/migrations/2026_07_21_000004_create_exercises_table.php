<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160)->index();
            $table->text('description')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->smallInteger('sets')->nullable();
            $table->smallInteger('repetitions')->nullable();
            $table->text('material_url')->nullable();
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz()->index();
        });

        DB::statement("ALTER TABLE exercises ADD CONSTRAINT exercises_name_not_blank CHECK (btrim(name) <> '')");
        DB::statement('ALTER TABLE exercises ADD CONSTRAINT exercises_duration_positive CHECK (duration_seconds IS NULL OR duration_seconds > 0)');
        DB::statement('ALTER TABLE exercises ADD CONSTRAINT exercises_sets_positive CHECK (sets IS NULL OR sets > 0)');
        DB::statement('ALTER TABLE exercises ADD CONSTRAINT exercises_repetitions_positive CHECK (repetitions IS NULL OR repetitions > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
