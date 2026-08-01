<?php

namespace App\Modules\Exercises\Support;

final class ExerciseData
{
    public static function requiredText(mixed $value): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }

    public static function optionalText(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    public static function optionalInteger(mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }

    public static function formatDuration(?int $seconds): string
    {
        if ($seconds === null) {
            return 'No configurada';
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes === 0) {
            return $remainingSeconds.' s';
        }

        if ($remainingSeconds === 0) {
            return $minutes.' min';
        }

        return $minutes.' min '.$remainingSeconds.' s';
    }
}
