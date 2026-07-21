<?php

namespace App\Http\Requests\Exercises;

class UpdateExerciseRequest extends ExerciseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ...$this->exerciseRules(),
            'updated_at' => ['required', 'date'],
        ];
    }
}
