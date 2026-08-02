<?php

namespace App\Modules\Reminders\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

class ProcessDueReminders
{
    public function __construct(
        private readonly FindDueReminderSchedules $findDue,
        private readonly AcquireReminderExecution $acquire,
        private readonly ProcessReminderExecution $process,
    ) {}

    /** @return array{due: int, acquired: int, skipped: int} */
    public function handle(?CarbonImmutable $now = null): array
    {
        $localNow = ($now ?? CarbonImmutable::now())->setTimezone('America/Lima');
        $schedules = $this->findDue->handle($localNow);
        $acquired = 0;
        $skipped = 0;

        foreach ($schedules as $schedule) {
            $execution = $this->acquire->handle($schedule, $localNow);
            if (! $execution) {
                $skipped++;
                Log::info('ALREADY_PROCESSED', [
                    'plan_id' => $schedule->configuration->plan_id,
                    'scheduled_local_date' => $localNow->toDateString(),
                    'scheduled_local_time' => substr((string) $schedule->send_at, 0, 5),
                ]);

                continue;
            }

            $acquired++;
            $this->process->handle($execution);
        }

        return ['due' => $schedules->count(), 'acquired' => $acquired, 'skipped' => $skipped];
    }
}
