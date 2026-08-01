<?php

namespace App\Modules\Exercises\Actions;

use App\Models\Exercise;
use Illuminate\Support\Facades\DB;

class RetireExercise
{
    public function handle(int $exerciseId): bool
    {
        return DB::transaction(function () use ($exerciseId): bool {
            $exercise = Exercise::withTrashed()->lockForUpdate()->findOrFail($exerciseId);

            if ($exercise->trashed()) {
                return false;
            }

            $exercise->delete();

            return true;
        });
    }
}
