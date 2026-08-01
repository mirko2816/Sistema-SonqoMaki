<?php

namespace App\Modules\Plans\Actions;

use App\Models\Plan;
use App\Models\Routine;
use App\Models\RoutineTemplate;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CopyRoutineTemplate
{
    public function handle(Plan $plan, array $data): Routine
    {
        try {
            return DB::transaction(function () use ($plan, $data): Routine {
                $lockedPlan = Plan::query()->lockForUpdate()->findOrFail($plan->id);
                $template = RoutineTemplate::query()->where('status', RoutineTemplate::STATUS_ACTIVE)->lockForUpdate()->find($data['routine_template_id']);
                if (! $template) {
                    throw ValidationException::withMessages(['routine_template_id' => 'Selecciona una plantilla activa.']);
                }
                if ($data['starts_on'] < $lockedPlan->starts_on->format('Y-m-d') || $data['ends_on'] > $lockedPlan->ends_on->format('Y-m-d')) {
                    throw ValidationException::withMessages(['starts_on' => 'Las fechas deben quedar dentro del plan.']);
                }
                $routine = $lockedPlan->routines()->create(['name' => $template->name, 'starts_on' => $data['starts_on'], 'ends_on' => $data['ends_on']]);
                foreach ($template->exercises()->lockForUpdate()->get() as $copy) {
                    $routine->exercises()->create($copy->only(['source_exercise_id', 'position', 'name', 'description', 'duration_seconds', 'sets', 'repetitions', 'material_url']));
                }

                return $routine->load('exercises');
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23P01') {
                throw ValidationException::withMessages(['starts_on' => 'La plantilla se superpondría con otra rutina del plan.']);
            }
            throw $e;
        }
    }
}
