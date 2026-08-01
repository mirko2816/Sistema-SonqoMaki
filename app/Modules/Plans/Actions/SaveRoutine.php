<?php

namespace App\Modules\Plans\Actions;

use App\Models\Exercise;
use App\Models\Plan;
use App\Models\Routine;
use App\Modules\Plans\Support\PlanActivationValidator;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveRoutine
{
    public function __construct(private PlanActivationValidator $validator) {}

    public function handle(Plan $plan, ?Routine $routine, array $data): Routine
    {
        try {
            return DB::transaction(function () use ($plan, $routine, $data): Routine {
                $lockedPlan = Plan::query()->lockForUpdate()->findOrFail($plan->id);
                if ($lockedPlan->status === Plan::STATUS_FINISHED) {
                    throw ValidationException::withMessages(['routine' => 'Un plan finalizado no puede modificarse. Duplícalo para reutilizarlo.']);
                }
                if (CarbonImmutable::parse($data['starts_on'])->lt($lockedPlan->starts_on) || CarbonImmutable::parse($data['ends_on'])->gt($lockedPlan->ends_on)) {
                    throw ValidationException::withMessages(['starts_on' => 'La rutina debe quedar completamente dentro del rango del plan.']);
                }

                $item = $routine ? Routine::query()->where('plan_id', $lockedPlan->id)->lockForUpdate()->findOrFail($routine->id) : null;
                if ($item && ! $item->updated_at->equalTo(CarbonImmutable::parse($data['updated_at']))) {
                    throw ValidationException::withMessages(['updated_at' => 'La rutina cambió. Recarga antes de guardar.']);
                }
                $item ??= new Routine(['plan_id' => $lockedPlan->id]);
                $item->fill(Arr::only($data, ['name', 'starts_on', 'ends_on']))->save();

                $existing = $item->exercises()->lockForUpdate()->get()->keyBy('id');
                $kept = collect($data['exercises'])->pluck('id')->filter()->map(fn ($id) => (int) $id);
                $item->exercises()->whereNotIn('id', $kept)->delete();
                foreach ($existing->whereIn('id', $kept)->values() as $offset => $copy) {
                    $copy->update(['position' => 32000 + $offset]);
                }

                foreach ($data['exercises'] as $exerciseData) {
                    $values = Arr::only($exerciseData, ['position', 'name', 'description', 'duration_seconds', 'sets', 'repetitions', 'material_url']);
                    if (! empty($exerciseData['id'])) {
                        $copy = $existing->get((int) $exerciseData['id']);
                        if (! $copy) {
                            throw ValidationException::withMessages(['exercises' => 'Uno de los ejercicios no pertenece a esta rutina.']);
                        }
                        $copy->update($values);
                    } else {
                        $source = Exercise::query()->lockForUpdate()->find($exerciseData['source_exercise_id']);
                        if (! $source) {
                            throw ValidationException::withMessages(['exercises' => 'Uno de los ejercicios ya no está disponible.']);
                        }
                        $item->exercises()->create([...$values, 'source_exercise_id' => $source->id]);
                    }
                }

                if ($lockedPlan->status === Plan::STATUS_ACTIVE && ($problems = $this->validator->problems($lockedPlan->fresh())) !== []) {
                    throw ValidationException::withMessages(['plan' => $problems]);
                }

                return $item->fresh('exercises');
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23P01') {
                throw ValidationException::withMessages(['starts_on' => 'La rutina se superpone con otra rutina del plan.']);
            }
            throw ValidationException::withMessages(['routine' => 'No se pudo guardar la rutina. Ningún cambio fue aplicado.']);
        }
    }
}
