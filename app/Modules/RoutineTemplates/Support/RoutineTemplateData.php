<?php

namespace App\Modules\RoutineTemplates\Support;

use App\Modules\Exercises\Support\ExerciseData;

final class RoutineTemplateData
{
    public static function normalize(array $data): array
    {
        $data['name'] = ExerciseData::requiredText($data['name'] ?? null);
        $exercises = $data['exercises'] ?? [];
        if (! is_array($exercises)) {
            $data['exercises'] = $exercises;

            return $data;
        }

        $data['exercises'] = array_values($exercises);

        foreach ($data['exercises'] as $index => $exercise) {
            if (! is_array($exercise)) {
                continue;
            }

            $data['exercises'][$index] = [
                ...$exercise,
                'name' => ExerciseData::requiredText($exercise['name'] ?? null),
                'description' => ExerciseData::optionalText($exercise['description'] ?? null),
                'duration_seconds' => ExerciseData::optionalInteger($exercise['duration_seconds'] ?? null),
                'sets' => ExerciseData::optionalInteger($exercise['sets'] ?? null),
                'repetitions' => ExerciseData::optionalInteger($exercise['repetitions'] ?? null),
                'material_url' => ExerciseData::optionalText($exercise['material_url'] ?? null),
            ];
        }

        return $data;
    }
}
