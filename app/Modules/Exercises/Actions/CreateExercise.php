<?php

namespace App\Modules\Exercises\Actions;

use App\Models\Exercise;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateExercise
{
    public function handle(array $data): Exercise
    {
        try {
            return DB::transaction(fn (): Exercise => Exercise::create($data));
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'name' => 'No se pudo registrar el ejercicio. Revisa los datos e inténtalo nuevamente.',
            ]);
        }
    }
}
