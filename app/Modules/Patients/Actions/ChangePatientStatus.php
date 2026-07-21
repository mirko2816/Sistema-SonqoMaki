<?php

namespace App\Modules\Patients\Actions;

use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class ChangePatientStatus
{
    public function handle(Patient $patient, string $status): Patient
    {
        return DB::transaction(function () use ($patient, $status): Patient {
            $lockedPatient = Patient::query()->lockForUpdate()->findOrFail($patient->getKey());

            if ($lockedPatient->status !== $status) {
                $lockedPatient->update(['status' => $status]);
            }

            return $lockedPatient;
        });
    }
}
