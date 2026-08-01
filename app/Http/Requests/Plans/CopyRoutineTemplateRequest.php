<?php

namespace App\Http\Requests\Plans;

use Illuminate\Foundation\Http\FormRequest;

class CopyRoutineTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return ['routine_template_id' => ['required', 'integer'], 'starts_on' => ['required', 'date_format:Y-m-d'], 'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on']];
    }
}
