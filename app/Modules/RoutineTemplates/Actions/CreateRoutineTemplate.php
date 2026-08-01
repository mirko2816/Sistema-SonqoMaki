<?php

namespace App\Modules\RoutineTemplates\Actions;

use App\Models\Exercise;
use App\Models\RoutineTemplate;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateRoutineTemplate
{
    public function handle(array $data): RoutineTemplate
    {
        try {
            return DB::transaction(function () use ($data): RoutineTemplate {
                $template = RoutineTemplate::create([
                    'name' => $data['name'],
                    'status' => RoutineTemplate::STATUS_ACTIVE,
                ]);

                foreach ($data['exercises'] as $item) {
                    $source = Exercise::query()->lockForUpdate()->find($item['source_exercise_id']);
                    if (! $source) {
                        throw ValidationException::withMessages([
                            'exercises' => 'Uno de los ejercicios ya no está disponible. Revisa la composición.',
                        ]);
                    }

                    $template->exercises()->create($this->copyData($item, $source));
                }

                return $template->load('exercises');
            });
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'exercises' => 'No se pudo guardar la plantilla. Revisa sus datos e inténtalo nuevamente.',
            ]);
        }
    }

    private function copyData(array $item, Exercise $source): array
    {
        $copied = Arr::only($source->getAttributes(), [
            'name', 'description', 'duration_seconds', 'sets', 'repetitions', 'material_url',
        ]);

        return [
            ...$copied,
            ...Arr::only($item, ['position', 'name', 'description', 'duration_seconds', 'sets', 'repetitions', 'material_url']),
            'source_exercise_id' => $source->getKey(),
        ];
    }
}
