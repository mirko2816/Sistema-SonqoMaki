<?php

namespace App\Http\Requests\Patients;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangePatientStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in([Patient::STATUS_ACTIVE, Patient::STATUS_INACTIVE])]];
    }
}
