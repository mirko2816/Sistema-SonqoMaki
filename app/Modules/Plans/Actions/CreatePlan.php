<?php

namespace App\Modules\Plans\Actions;

use App\Models\Patient;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePlan
{
    public function handle(array $data): Plan
    {
        return DB::transaction(function () use ($data): Plan {
            $patient = Patient::query()->lockForUpdate()->find($data['patient_id']);
            if (! $patient || $patient->status !== Patient::STATUS_ACTIVE) {
                throw ValidationException::withMessages(['patient_id' => 'Selecciona un paciente activo y no archivado.']);
            }

            return Plan::create([...$data, 'status' => Plan::STATUS_PAUSED]);
        });
    }
}
