<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_executions', function (Blueprint $table) {
            $table->id();
            $table->uuid('correlation_id')->unique();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('reminder_schedule_id')->nullable()->constrained('reminder_schedules')->restrictOnDelete();
            $table->foreignId('routine_id')->nullable()->constrained('routines')->restrictOnDelete();
            $table->foreignId('public_link_id')->nullable()->constrained('public_links')->restrictOnDelete();
            $table->date('scheduled_local_date');
            $table->time('scheduled_local_time', 0);
            $table->timestampTz('scheduled_at');
            $table->timestampTz('started_at')->index();
            $table->timestampTz('completed_at')->nullable();
            $table->string('outcome', 20)->index();
            $table->string('reason_code', 60)->nullable();
            $table->string('patient_name_snapshot', 241);
            $table->string('recipient_phone_snapshot', 12);
            $table->string('plan_name_snapshot', 160);
            $table->string('whatsapp_message_id', 255)->nullable();
            $table->smallInteger('provider_http_status')->nullable();
            $table->string('provider_error_code', 100)->nullable();
            $table->text('provider_error_detail')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->unique(
                ['plan_id', 'scheduled_local_date', 'scheduled_local_time'],
                'reminder_executions_business_key_unique'
            );
            $table->index('patient_id');
            $table->index('plan_id');
        });

        DB::statement("ALTER TABLE reminder_executions ADD CONSTRAINT reminder_executions_outcome_valid CHECK (outcome IN ('processing', 'omitted', 'accepted', 'failed'))");
        DB::statement('ALTER TABLE reminder_executions ADD CONSTRAINT reminder_executions_duration_non_negative CHECK (duration_ms IS NULL OR duration_ms >= 0)');
        DB::statement('CREATE UNIQUE INDEX reminder_executions_whatsapp_message_unique ON reminder_executions (whatsapp_message_id) WHERE whatsapp_message_id IS NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_executions');
    }
};
