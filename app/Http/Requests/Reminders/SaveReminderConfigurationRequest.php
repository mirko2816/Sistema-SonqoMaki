<?php

namespace App\Http\Requests\Reminders;

use Illuminate\Foundation\Http\FormRequest;

class SaveReminderConfigurationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'schedules' => is_array($this->input('schedules')) ? $this->input('schedules') : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
            'schedules' => ['present', 'array'],
            'schedules.*' => ['array', 'max:2'],
            'schedules.*.*' => ['nullable', 'string', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'schedules.array' => 'La programación enviada no es válida.',
            'schedules.*.max' => 'Solo puedes guardar hasta dos horarios por día.',
            'schedules.*.*.date_format' => 'Cada horario debe tener el formato de hora HH:MM.',
        ];
    }
}
