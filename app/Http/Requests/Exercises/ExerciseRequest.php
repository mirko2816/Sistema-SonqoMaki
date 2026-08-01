<?php

namespace App\Http\Requests\Exercises;

use App\Modules\Exercises\Support\ExerciseData;
use Illuminate\Foundation\Http\FormRequest;

abstract class ExerciseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => ExerciseData::requiredText($this->input('name')),
            'description' => ExerciseData::optionalText($this->input('description')),
            'duration_seconds' => ExerciseData::optionalInteger($this->input('duration_seconds')),
            'sets' => ExerciseData::optionalInteger($this->input('sets')),
            'repetitions' => ExerciseData::optionalInteger($this->input('repetitions')),
            'material_url' => ExerciseData::optionalText($this->input('material_url')),
        ]);
    }

    protected function exerciseRules(): array
    {
        return [
            'name' => ['bail', 'required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
            'sets' => ['nullable', 'integer', 'min:1', 'max:32767'],
            'repetitions' => ['nullable', 'integer', 'min:1', 'max:32767'],
            'material_url' => ['nullable', 'string', 'url:http,https'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'duration_seconds' => 'duración',
            'sets' => 'sets',
            'repetitions' => 'repeticiones',
            'material_url' => 'URL del material',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ingresa el nombre del ejercicio.',
            'name.max' => 'El nombre no puede superar 160 caracteres.',
            'duration_seconds.integer' => 'La duración debe ser un número entero de segundos.',
            'duration_seconds.min' => 'La duración debe ser mayor que cero.',
            'duration_seconds.max' => 'La duración supera el máximo permitido.',
            'sets.integer' => 'Los sets deben ser un número entero.',
            'sets.min' => 'Los sets deben ser mayores que cero.',
            'sets.max' => 'Los sets superan el máximo permitido.',
            'repetitions.integer' => 'Las repeticiones deben ser un número entero.',
            'repetitions.min' => 'Las repeticiones deben ser mayores que cero.',
            'repetitions.max' => 'Las repeticiones superan el máximo permitido.',
            'material_url.url' => 'Ingresa una URL completa que comience con http:// o https://.',
        ];
    }
}
