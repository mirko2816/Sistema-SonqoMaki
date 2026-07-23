<?php

namespace App\Modules\Plans\Actions;

use App\Models\Patient;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DuplicatePlan
{
    public function handle(Plan $source, array $data): Plan
    {
        return DB::transaction(function () use ($source, $data): Plan {
            $origin = Plan::query()->lockForUpdate()->findOrFail($source->id);
            $patient = Patient::query()->lockForUpdate()->find($data['patient_id']);
            if (! $patient || $patient->status !== Patient::STATUS_ACTIVE) {
                throw ValidationException::withMessages(['patient_id' => 'Selecciona un paciente activo y no archivado.']);
            }
            $copy = Plan::create(['patient_id' => $patient->id, 'name' => $data['name'], 'starts_on' => $origin->starts_on, 'ends_on' => $origin->ends_on, 'status' => Plan::STATUS_PAUSED]);
            foreach ($origin->routines()->with('exercises')->get() as $routine) {
                $routineCopy = $copy->routines()->create($routine->only(['name', 'starts_on', 'ends_on']));
                foreach ($routine->exercises as $exercise) {
                    $routineCopy->exercises()->create($exercise->only(['source_exercise_id', 'position', 'name', 'description', 'duration_seconds', 'sets', 'repetitions', 'material_url']));
                }
            }

            return $copy->load('routines.exercises');
        });
    }
}
