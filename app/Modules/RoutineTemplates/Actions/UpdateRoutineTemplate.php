<?php

namespace App\Modules\RoutineTemplates\Actions;

use App\Models\Exercise;
use App\Models\RoutineTemplate;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateRoutineTemplate
{
    public function handle(RoutineTemplate $template, array $data): RoutineTemplate
    {
        try {
            return DB::transaction(function () use ($template, $data): RoutineTemplate {
                $locked = RoutineTemplate::query()->lockForUpdate()->findOrFail($template->getKey());
                if (! $locked->updated_at->equalTo(CarbonImmutable::parse($data['updated_at']))) {
                    throw ValidationException::withMessages([
                        'updated_at' => 'Esta plantilla cambió desde que abriste el formulario. Recarga la página antes de continuar.',
                    ]);
                }

                $existing = $locked->exercises()->lockForUpdate()->get()->keyBy('id');
                $keptIds = collect($data['exercises'])->pluck('id')->filter()->map(fn ($id) => (int) $id);

                $locked->update(['name' => $data['name']]);
                $locked->exercises()->whereNotIn('id', $keptIds)->delete();

                // Libera las posiciones definitivas sin violar el índice único parcial.
                foreach ($existing->whereIn('id', $keptIds)->values() as $offset => $copy) {
                    $copy->update(['position' => 32000 + $offset]);
                }

                foreach ($data['exercises'] as $item) {
                    $values = Arr::only($item, [
                        'position', 'name', 'description', 'duration_seconds', 'sets', 'repetitions', 'material_url',
                    ]);

                    if (! empty($item['id'])) {
                        $existing->get((int) $item['id'])->update($values);

                        continue;
                    }

                    $source = Exercise::query()->lockForUpdate()->find($item['source_exercise_id']);
                    if (! $source) {
                        throw ValidationException::withMessages([
                            'exercises' => 'Uno de los ejercicios ya no está disponible. Revisa la composición.',
                        ]);
                    }

                    $locked->exercises()->create([...$values, 'source_exercise_id' => $source->getKey()]);
                }

                return $locked->load('exercises');
            });
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'exercises' => 'No se pudo actualizar la plantilla. Ningún cambio fue aplicado.',
            ]);
        }
    }
}
