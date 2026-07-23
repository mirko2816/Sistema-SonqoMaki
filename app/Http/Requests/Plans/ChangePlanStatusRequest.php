<?php

namespace App\Http\Requests\Plans;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;

class ChangePlanStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return ['status' => ['required', 'in:'.implode(',', [Plan::STATUS_ACTIVE, Plan::STATUS_PAUSED, Plan::STATUS_FINISHED])]];
    }
}
