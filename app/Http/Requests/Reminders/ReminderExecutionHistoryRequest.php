<?php

namespace App\Http\Requests\Reminders;

use App\Modules\Reminders\Enums\ReminderOutcome;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReminderExecutionHistoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'outcome' => ['nullable', Rule::enum(ReminderOutcome::class)],
            'patient' => ['nullable', 'integer', 'min:1'],
            'plan' => ['nullable', 'integer', 'min:1'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => [
                'nullable',
                'date_format:Y-m-d',
                Rule::when($this->filled('date_from'), 'after_or_equal:date_from'),
            ],
        ];
    }
}
