<?php

namespace App\Http\Requests\Patients;

class StorePatientRequest extends PatientRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->patientRules();
    }
}
