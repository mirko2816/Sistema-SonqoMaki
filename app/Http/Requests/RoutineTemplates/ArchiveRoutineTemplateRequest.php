<?php

namespace App\Http\Requests\RoutineTemplates;

use Illuminate\Foundation\Http\FormRequest;

class ArchiveRoutineTemplateRequest extends FormRequest
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
        return ['archive_confirmed.accepted' => 'Confirma que deseas archivar la plantilla.'];
    }
}
