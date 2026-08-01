<?php

namespace App\Http\Requests\Patients;

use App\Models\Patient;
use App\Modules\Patients\Support\PatientDataNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class PatientRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_names' => PatientDataNormalizer::names($this->input('first_names')),
            'last_names' => PatientDataNormalizer::names($this->input('last_names')),
            'dni' => PatientDataNormalizer::dni($this->input('dni')),
            'whatsapp_phone' => PatientDataNormalizer::phone($this->input('whatsapp_phone')),
            'whatsapp_consented_on' => $this->filled('whatsapp_consented_on')
                ? $this->input('whatsapp_consented_on')
                : null,
        ]);
    }

    protected function patientRules(?Patient $patient = null): array
    {
        return [
            'first_names' => ['bail', 'required', 'string', 'max:120'],
            'last_names' => ['bail', 'required', 'string', 'max:120'],
            'dni' => ['nullable', 'regex:/^[0-9]{8}$/', Rule::unique('patients', 'dni')->ignore($patient)],
            'whatsapp_phone' => ['bail', 'required', 'regex:/^\+51[0-9]{9}$/', Rule::unique('patients', 'whatsapp_phone')->ignore($patient)],
            'whatsapp_consented_on' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'status' => ['required', Rule::in([Patient::STATUS_ACTIVE, Patient::STATUS_INACTIVE])],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_names' => 'nombres',
            'last_names' => 'apellidos',
            'dni' => 'DNI',
            'whatsapp_phone' => 'teléfono de WhatsApp',
            'whatsapp_consented_on' => 'fecha de consentimiento',
            'status' => 'estado',
        ];
    }

    public function messages(): array
    {
        return [
            'first_names.required' => 'Ingresa los nombres del paciente.',
            'first_names.max' => 'Los nombres no pueden superar 120 caracteres.',
            'last_names.required' => 'Ingresa los apellidos del paciente.',
            'last_names.max' => 'Los apellidos no pueden superar 120 caracteres.',
            'dni.regex' => 'El DNI debe contener exactamente 8 dígitos.',
            'dni.unique' => 'Este DNI ya pertenece a otro paciente, incluso si está archivado.',
            'whatsapp_phone.required' => 'Ingresa el teléfono de WhatsApp del paciente.',
            'whatsapp_phone.regex' => 'Ingresa un teléfono peruano de 9 dígitos, con o sin el prefijo +51.',
            'whatsapp_phone.unique' => 'Este teléfono ya pertenece a otro paciente, incluso si está archivado.',
            'whatsapp_consented_on.date_format' => 'Ingresa una fecha de consentimiento válida.',
            'whatsapp_consented_on.before_or_equal' => 'La fecha de consentimiento no puede ser futura.',
            'status.required' => 'Selecciona el estado del paciente.',
            'status.in' => 'Selecciona un estado válido.',
        ];
    }
}
