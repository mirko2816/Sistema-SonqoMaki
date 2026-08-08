<?php

namespace App\Modules\Reminders\Actions;

use App\Models\ReminderExecution;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SearchReminderExecutionHistory
{
    public function handle(array $filters): LengthAwarePaginator
    {
        return ReminderExecution::query()
            ->with([
                'schedule:id,reminder_configuration_id,weekday,send_at,deleted_at',
                'routine:id,plan_id,name,deleted_at',
            ])
            ->when($filters['outcome'] ?? null, fn ($query, $outcome) => $query->where('outcome', $outcome))
            ->when($filters['patient'] ?? null, fn ($query, $patient) => $query->where('patient_id', $patient))
            ->when($filters['plan'] ?? null, fn ($query, $plan) => $query->where('plan_id', $plan))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('scheduled_local_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('scheduled_local_date', '<=', $date))
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function patientOptions(): Collection
    {
        return ReminderExecution::query()
            ->selectRaw('patient_id, max(patient_name_snapshot) as label')
            ->groupBy('patient_id')
            ->orderBy('label')
            ->get();
    }

    public function planOptions(): Collection
    {
        return ReminderExecution::query()
            ->selectRaw('plan_id, max(plan_name_snapshot) as label')
            ->groupBy('plan_id')
            ->orderBy('label')
            ->get();
    }
}
