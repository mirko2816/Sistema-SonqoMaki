<?php

namespace App\Http\Requests\Plans;

class UpdatePlanRequest extends StorePlanRequest
{
    public function rules(): array
    {
        return [...parent::rules(), 'patient_id' => ['prohibited'], 'updated_at' => ['required', 'date']];
    }
}
