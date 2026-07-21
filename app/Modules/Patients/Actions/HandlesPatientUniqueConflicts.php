<?php

namespace App\Modules\Patients\Actions;

use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

trait HandlesPatientUniqueConflicts
{
    protected function rethrowUniqueConflict(QueryException $exception): never
    {
        if (($exception->errorInfo[0] ?? null) !== '23505') {
            throw $exception;
        }

        $message = $exception->getMessage();
        $field = str_contains($message, 'patients_dni_unique') ? 'dni' : 'whatsapp_phone';

        throw ValidationException::withMessages([
            $field => $field === 'dni'
                ? 'Este DNI ya pertenece a otro paciente, incluso si está archivado.'
                : 'Este teléfono ya pertenece a otro paciente, incluso si está archivado.',
        ]);
    }
}
