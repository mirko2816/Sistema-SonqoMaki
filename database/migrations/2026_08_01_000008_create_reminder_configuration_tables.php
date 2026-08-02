<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->unique()->constrained('plans')->restrictOnDelete();
            $table->boolean('is_active')->default(false);
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
        });

        DB::statement(<<<'SQL'
            INSERT INTO reminder_configurations (plan_id, is_active, created_at, updated_at)
            SELECT id, FALSE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            FROM plans
            ON CONFLICT (plan_id) DO NOTHING
        SQL);

        Schema::create('reminder_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reminder_configuration_id')->constrained('reminder_configurations')->restrictOnDelete();
            $table->smallInteger('weekday');
            $table->time('send_at', 0);
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz();
            $table->index(['reminder_configuration_id', 'weekday'], 'reminder_schedules_configuration_weekday_index');
            $table->index('deleted_at');
        });

        DB::statement('ALTER TABLE reminder_schedules ADD CONSTRAINT reminder_schedules_weekday_valid CHECK (weekday BETWEEN 1 AND 7)');
        DB::statement('CREATE UNIQUE INDEX reminder_schedules_active_unique ON reminder_schedules (reminder_configuration_id, weekday, send_at) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_schedules');
        Schema::dropIfExists('reminder_configurations');
    }
};
