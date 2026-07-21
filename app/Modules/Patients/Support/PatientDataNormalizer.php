<?php

namespace App\Modules\Patients\Support;

final class PatientDataNormalizer
{
    public static function names(mixed $value): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }

    public static function dni(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    public static function phone(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $phone = preg_replace('/[\s\-().]/u', '', trim($value));

        if (preg_match('/^[0-9]{9}$/', $phone) === 1) {
            return '+51'.$phone;
        }

        if (preg_match('/^51[0-9]{9}$/', $phone) === 1) {
            return '+'.$phone;
        }

        return $phone;
    }
}
