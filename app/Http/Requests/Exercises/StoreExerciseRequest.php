<?php

namespace App\Http\Requests\Exercises;

class StoreExerciseRequest extends ExerciseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->exerciseRules();
    }
}
