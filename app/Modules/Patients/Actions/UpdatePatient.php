<?php

namespace App\Modules\Patients\Actions;

use App\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdatePatient
{
    use HandlesPatientUniqueConflicts;

    public function handle(Patient $patient, array $data): Patient
    {
        try {
            return DB::transaction(function () use ($patient, $data): Patient {
                $lockedPatient = Patient::query()->lockForUpdate()->findOrFail($patient->getKey());

                if (! $lockedPatient->updated_at->equalTo(CarbonImmutable::parse($data['updated_at']))) {
                    throw ValidationException::withMessages([
                        'updated_at' => 'Este paciente cambió desde que abriste el formulario. Recarga la página antes de continuar.',
                    ]);
                }

                $lockedPatient->update(Arr::except($data, ['updated_at', 'consent_removal_confirmed']));

                return $lockedPatient;
            });
        } catch (QueryException $exception) {
            $this->rethrowUniqueConflict($exception);
        }
    }
}
