<?php

namespace App\Modules\Reminders\Actions;

use App\Models\Plan;
use App\Models\ReminderConfiguration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveReminderConfiguration
{
    public function handle(Plan $plan, array $data): ReminderConfiguration
    {
        $normalized = $this->normalize($data['schedules'] ?? []);

        try {
            return DB::transaction(function () use ($plan, $data, $normalized): ReminderConfiguration {
                $configuration = ReminderConfiguration::query()
                    ->where('plan_id', $plan->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $existing = $configuration->schedules()->withTrashed()->get()
                    ->keyBy(fn ($schedule) => $schedule->weekday.'|'.substr($schedule->send_at, 0, 5));
                $wanted = collect($normalized)->keyBy(fn (array $schedule) => $schedule['weekday'].'|'.$schedule['send_at']);

                $keptIds = $existing->filter(fn ($schedule, $key) => $wanted->has($key))->pluck('id');
                $configuration->schedules()->whereNotIn('id', $keptIds)->delete();

                foreach ($wanted as $key => $schedule) {
                    $stored = $existing->get($key);
                    if ($stored) {
                        if ($stored->trashed()) {
                            $stored->restore();
                        }
                    } else {
                        $configuration->schedules()->create($schedule);
                    }
                }

                $configuration->update(['is_active' => (bool) ($data['is_active'] ?? false)]);

                return $configuration->fresh('schedules');
            });
        } catch (QueryException $exception) {
            if (in_array($exception->getCode(), ['23505', '23514'], true)) {
                throw ValidationException::withMessages([
                    'schedules' => 'La programación entra en conflicto con otro cambio. Recarga la página y vuelve a intentarlo.',
                ]);
            }

            throw $exception;
        }
    }

    private function normalize(array $schedules): array
    {
        $normalized = [];
        foreach ($schedules as $weekday => $times) {
            if (! ctype_digit((string) $weekday) || (int) $weekday < 1 || (int) $weekday > 7) {
                throw ValidationException::withMessages(['schedules' => 'El día seleccionado no es válido. Usa los días del lunes al domingo.']);
            }
            if (! is_array($times)) {
                throw ValidationException::withMessages(['schedules' => 'La programación enviada no es válida.']);
            }

            $dayTimes = [];
            foreach ($times as $time) {
                $time = trim((string) $time);
                if ($time === '') {
                    continue;
                }
                if (! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
                    throw ValidationException::withMessages(["schedules.{$weekday}" => 'Ingresa horarios válidos en formato HH:MM.']);
                }
                if (in_array($time, $dayTimes, true)) {
                    throw ValidationException::withMessages(["schedules.{$weekday}" => 'No puedes repetir el mismo horario en un día.']);
                }
                $dayTimes[] = $time;
            }
            if (count($dayTimes) > 2) {
                throw ValidationException::withMessages(["schedules.{$weekday}" => 'Solo puedes guardar hasta dos horarios por día.']);
            }
            sort($dayTimes);
            foreach ($dayTimes as $time) {
                $normalized[] = ['weekday' => (int) $weekday, 'send_at' => $time];
            }
        }

        return $normalized;
    }
}
