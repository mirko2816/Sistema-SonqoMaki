<?php

namespace App\Http\Requests\Plans;

use Illuminate\Foundation\Http\FormRequest;

class DuplicatePlanRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        return ['patient_id' => ['required', 'integer'], 'name' => ['required', 'string', 'max:160']];
    }
}
