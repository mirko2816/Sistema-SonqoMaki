<?php

namespace App\Http\Requests\RoutineTemplates;

class UpdateRoutineTemplateRequest extends RoutineTemplateRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [...$this->templateRules(), 'updated_at' => ['required', 'date']];
    }
}
