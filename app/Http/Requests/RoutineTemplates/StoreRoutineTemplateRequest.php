<?php

namespace App\Http\Requests\RoutineTemplates;

class StoreRoutineTemplateRequest extends RoutineTemplateRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->templateRules();
    }
}
