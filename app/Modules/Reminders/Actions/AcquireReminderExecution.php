<?php

namespace App\Modules\Reminders\Actions;

use App\Models\ReminderExecution;
use App\Models\ReminderSchedule;
use App\Modules\Reminders\Enums\ReminderOutcome;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AcquireReminderExecution
{
    public function handle(ReminderSchedule $schedule, CarbonImmutable $localNow): ?ReminderExecution
    {
        $schedule->loadMissing('configuration.plan.patient');
        $plan = $schedule->configuration->plan;
        $patient = $plan->patient;
        $localDate = $localNow->setTimezone('America/Lima')->toDateString();
        $localTime = substr((string) $schedule->send_at, 0, 5).':00';
        $scheduledAt = CarbonImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $localDate.' '.$localTime,
            'America/Lima'
        )->utc();
        $startedAt = CarbonImmutable::now('UTC');
        $correlationId = (string) Str::uuid();

        $row = DB::selectOne(<<<'SQL'
            INSERT INTO reminder_executions (
                correlation_id, plan_id, patient_id, reminder_schedule_id,
                scheduled_local_date, scheduled_local_time, scheduled_at, started_at,
                outcome, patient_name_snapshot, recipient_phone_snapshot, plan_name_snapshot,
                created_at, updated_at
            ) VALUES (
                :correlation_id, :plan_id, :patient_id, :reminder_schedule_id,
                :scheduled_local_date, :scheduled_local_time, :scheduled_at, :started_at,
                :outcome, :patient_name_snapshot, :recipient_phone_snapshot, :plan_name_snapshot,
                :created_at, :updated_at
            )
            ON CONFLICT (plan_id, scheduled_local_date, scheduled_local_time) DO NOTHING
            RETURNING id
        SQL, [
            'correlation_id' => $correlationId,
            'plan_id' => $plan->getKey(),
            'patient_id' => $patient->getKey(),
            'reminder_schedule_id' => $schedule->getKey(),
            'scheduled_local_date' => $localDate,
            'scheduled_local_time' => $localTime,
            'scheduled_at' => $scheduledAt->format('Y-m-d H:i:sP'),
            'started_at' => $startedAt->format('Y-m-d H:i:s.uP'),
            'outcome' => ReminderOutcome::Processing->value,
            'patient_name_snapshot' => $patient->full_name,
            'recipient_phone_snapshot' => $patient->whatsapp_phone,
            'plan_name_snapshot' => $plan->name,
            'created_at' => $startedAt->format('Y-m-d H:i:s.uP'),
            'updated_at' => $startedAt->format('Y-m-d H:i:s.uP'),
        ]);

        return $row ? ReminderExecution::findOrFail($row->id) : null;
    }
}
