<?php

namespace App\Modules\Patients\Actions;

use App\Models\Patient;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CreatePatient
{
    use HandlesPatientUniqueConflicts;

    public function handle(array $data): Patient
    {
        try {
            return DB::transaction(fn (): Patient => Patient::create($data));
        } catch (QueryException $exception) {
            $this->rethrowUniqueConflict($exception);
        }
    }
}
