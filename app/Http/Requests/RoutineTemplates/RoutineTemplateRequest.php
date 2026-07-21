<?php

namespace App\Http\Requests\RoutineTemplates;

use App\Models\Exercise;
use App\Models\RoutineTemplateExercise;
use App\Modules\RoutineTemplates\Support\RoutineTemplateData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class RoutineTemplateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->replace(RoutineTemplateData::normalize($this->all()));
    }

    protected function templateRules(): array
    {
        return [
            'name' => ['bail', 'required', 'string', 'max:160'],
            'exercises' => ['present', 'array', 'max:100'],
            'exercises.*' => ['array'],
            'exercises.*.id' => ['nullable', 'integer'],
            'exercises.*.source_exercise_id' => ['nullable', 'integer'],
            'exercises.*.position' => ['required', 'integer', 'min:1', 'max:100'],
            'exercises.*.name' => ['bail', 'required', 'string', 'max:160'],
            'exercises.*.description' => ['nullable', 'string'],
            'exercises.*.duration_seconds' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
            'exercises.*.sets' => ['nullable', 'integer', 'min:1', 'max:32767'],
            'exercises.*.repetitions' => ['nullable', 'integer', 'min:1', 'max:32767'],
            'exercises.*.material_url' => ['nullable', 'string', 'url:http,https'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $items = $this->input('exercises', []);
            if (! is_array($items) || collect($items)->contains(fn ($item) => ! is_array($item))) {
                return;
            }
            $positions = array_map(
                fn (array $item): mixed => isset($item['position']) && is_numeric($item['position'])
                    ? (int) $item['position']
                    : $item['position'] ?? null,
                $items,
            );

            $expectedPositions = $items === [] ? [] : range(1, count($items));
            if ($positions !== $expectedPositions) {
                $validator->errors()->add('exercises', 'El orden de los ejercicios fue manipulado. Recarga el formulario e inténtalo nuevamente.');
            }

            $template = $this->route('routine_template');
            foreach ($items as $index => $item) {
                $copyId = $item['id'] ?? null;
                if ($copyId !== null) {
                    $belongs = $template && RoutineTemplateExercise::query()
                        ->whereKey($copyId)->where('routine_template_id', $template->getKey())->exists();
                    if (! $belongs) {
                        $validator->errors()->add("exercises.{$index}.id", 'El ejercicio de plantilla no es válido.');
                    }
                } elseif (! isset($item['source_exercise_id']) || ! Exercise::query()->whereKey($item['source_exercise_id'])->exists()) {
                    $validator->errors()->add("exercises.{$index}.source_exercise_id", 'Selecciona un ejercicio activo de la biblioteca.');
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ingresa el nombre de la plantilla.',
            'name.max' => 'El nombre no puede superar 160 caracteres.',
            'exercises.max' => 'Una plantilla admite hasta 100 ejercicios.',
            'exercises.*.name.required' => 'Cada ejercicio debe conservar un nombre.',
            'exercises.*.material_url.url' => 'Cada material debe usar una URL http:// o https:// válida.',
            'exercises.*.duration_seconds.min' => 'La duración debe ser mayor que cero.',
            'exercises.*.sets.min' => 'Los sets deben ser mayores que cero.',
            'exercises.*.repetitions.min' => 'Las repeticiones deben ser mayores que cero.',
        ];
    }
}
