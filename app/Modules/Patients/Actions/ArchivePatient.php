<?php

namespace App\Modules\Patients\Actions;

use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class ArchivePatient
{
    public function handle(Patient $patient): void
    {
        DB::transaction(function () use ($patient): void {
            $lockedPatient = Patient::query()->lockForUpdate()->findOrFail($patient->getKey());
            $lockedPatient->update(['status' => Patient::STATUS_INACTIVE]);

            // Los efectos sobre planes, recordatorios y enlaces se incorporarán aquí
            // cuando existan esos módulos y sus tablas, dentro de esta transacción.
            $lockedPatient->delete();
        });
    }
}
