<?php

namespace App\Http\Requests\Plans;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        return ['patient_id' => ['required', 'integer'], 'name' => ['required', 'string', 'max:160'], 'starts_on' => ['required', 'date_format:Y-m-d'], 'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on']];
    }

    public function messages(): array
    {
        return ['patient_id.required' => 'Selecciona un paciente.', 'name.required' => 'Ingresa el nombre del plan.', 'ends_on.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.'];
    }
}
