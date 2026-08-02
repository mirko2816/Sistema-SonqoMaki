<?php

namespace App\Modules\Reminders\Actions;

use App\Models\ReminderSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class FindDueReminderSchedules
{
    /** @return Collection<int, ReminderSchedule> */
    public function handle(?CarbonImmutable $now = null): Collection
    {
        $localNow = ($now ?? CarbonImmutable::now())->setTimezone('America/Lima');

        return ReminderSchedule::query()
            ->where('weekday', $localNow->dayOfWeekIso)
            ->whereTime('send_at', $localNow->format('H:i:00'))
            ->whereHas(
                'configuration.plan.patient',
                fn ($query) => $query->whereNull('patients.deleted_at')
            )
            ->with([
                'configuration.plan.patient',
            ])
            ->orderBy('id')
            ->get();
    }
}
