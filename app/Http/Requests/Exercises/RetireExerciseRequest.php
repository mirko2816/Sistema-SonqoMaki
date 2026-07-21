<?php

namespace App\Http\Requests\Exercises;

use Illuminate\Foundation\Http\FormRequest;

class RetireExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['retirement_confirmed' => ['required', 'accepted']];
    }

    public function messages(): array
    {
        return [
            'retirement_confirmed.required' => 'Confirma que comprendes el efecto de retirar el ejercicio.',
            'retirement_confirmed.accepted' => 'Confirma que comprendes el efecto de retirar el ejercicio.',
        ];
    }
}
