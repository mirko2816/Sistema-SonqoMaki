<?php

namespace App\Http\Requests\Patients;

use Illuminate\Foundation\Http\FormRequest;

class ArchivePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['archive_confirmed' => ['accepted']];
    }

    public function messages(): array
    {
        return ['archive_confirmed.accepted' => 'Debes confirmar que comprendes el efecto de archivar al paciente.'];
    }
}
