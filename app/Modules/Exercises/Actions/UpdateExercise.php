<?php

namespace App\Modules\Exercises\Actions;

use App\Models\Exercise;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateExercise
{
    public function handle(Exercise $exercise, array $data): Exercise
    {
        try {
            return DB::transaction(function () use ($exercise, $data): Exercise {
                $lockedExercise = Exercise::query()->lockForUpdate()->findOrFail($exercise->getKey());

                if (! $lockedExercise->updated_at->equalTo(CarbonImmutable::parse($data['updated_at']))) {
                    throw ValidationException::withMessages([
                        'updated_at' => 'Este ejercicio cambió desde que abriste el formulario. Recarga la página antes de continuar.',
                    ]);
                }

                $lockedExercise->update(Arr::except($data, 'updated_at'));

                return $lockedExercise;
            });
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'name' => 'No se pudo actualizar el ejercicio. Revisa los datos e inténtalo nuevamente.',
            ]);
        }
    }
}
