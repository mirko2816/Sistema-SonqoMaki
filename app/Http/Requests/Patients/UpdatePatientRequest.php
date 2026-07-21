<?php

namespace App\Http\Requests\Patients;

use App\Models\Patient;

class UpdatePatientRequest extends PatientRequest
{
    private bool $removingConsent = false;

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        /** @var Patient $patient */
        $patient = $this->route('patient');
        $this->removingConsent = $patient->whatsapp_consented_on !== null
            && $this->input('whatsapp_consented_on') === null;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Patient $patient */
        $patient = $this->route('patient');

        return [
            ...$this->patientRules($patient),
            'updated_at' => ['required', 'date'],
            'consent_removal_confirmed' => $this->removingConsent
                ? ['required', 'accepted']
                : ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'consent_removal_confirmed.required' => 'Confirma que deseas retirar el consentimiento y bloquear futuros envíos.',
            'consent_removal_confirmed.accepted' => 'Confirma que deseas retirar el consentimiento y bloquear futuros envíos.',
        ];
    }
}
