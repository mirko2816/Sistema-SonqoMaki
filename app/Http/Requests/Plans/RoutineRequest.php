<?php

namespace App\Http\Requests\Plans;

use App\Models\Exercise;
use App\Models\RoutineExercise;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RoutineRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $items = is_array($this->input('exercises')) ? $this->input('exercises') : $this->input('exercises');
        if ($items === null || $items === '') {
            $items = [];
        }
        if (is_array($items)) {
            foreach ($items as &$item) {
                if (is_array($item)) {
                    foreach (['name', 'description', 'material_url'] as $field) {
                        $item[$field] = isset($item[$field]) && trim((string) $item[$field]) !== '' ? trim((string) $item[$field]) : null;
                    }
                }
            }
        }
        $this->merge(['name' => trim((string) $this->input('name')), 'exercises' => $items]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'], 'starts_on' => ['required', 'date_format:Y-m-d'], 'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'], 'updated_at' => [$this->route('routine') ? 'required' : 'nullable', 'date'],
            'exercises' => ['present', 'array', 'max:100'], 'exercises.*' => ['array'], 'exercises.*.id' => ['nullable', 'integer'], 'exercises.*.source_exercise_id' => ['nullable', 'integer'],
            'exercises.*.position' => ['required', 'integer', 'min:1', 'max:100'], 'exercises.*.name' => ['required', 'string', 'max:160'], 'exercises.*.description' => ['nullable', 'string'],
            'exercises.*.duration_seconds' => ['nullable', 'integer', 'min:1', 'max:2147483647'], 'exercises.*.sets' => ['nullable', 'integer', 'min:1', 'max:32767'], 'exercises.*.repetitions' => ['nullable', 'integer', 'min:1', 'max:32767'], 'exercises.*.material_url' => ['nullable', 'url:http,https'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $items = $this->input('exercises', []);
            if (! is_array($items) || collect($items)->contains(fn ($item) => ! is_array($item))) {
                return;
            }
            $positions = collect($items)->pluck('position')->map(fn ($v) => is_numeric($v) ? (int) $v : $v)->all();
            if ($positions !== ($items === [] ? [] : range(1, count($items)))) {
                $validator->errors()->add('exercises', 'El orden de ejercicios fue manipulado.');
            }
            $routine = $this->route('routine');
            foreach ($items as $index => $item) {
                if (! empty($item['id'])) {
                    if (! $routine || ! RoutineExercise::query()->whereKey($item['id'])->where('routine_id', $routine->id)->exists()) {
                        $validator->errors()->add("exercises.{$index}.id", 'El ejercicio no pertenece a esta rutina.');
                    }
                } elseif (empty($item['source_exercise_id']) || ! Exercise::query()->whereKey($item['source_exercise_id'])->exists()) {
                    $validator->errors()->add("exercises.{$index}.source_exercise_id", 'Selecciona un ejercicio activo.');
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ingresa el nombre de la rutina.',
            'ends_on.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
            'exercises.array' => 'La composición de ejercicios no es válida.',
            'exercises.*.name.required' => 'Cada ejercicio debe conservar un nombre.',
            'exercises.*.material_url.url' => 'El material debe usar una URL http:// o https:// válida.',
        ];
    }
}
