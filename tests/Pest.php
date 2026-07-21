<?php

use App\Models\Exercise;
use App\Models\Patient;
use App\Models\User;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->in('Feature');

function specialist(array $attributes = []): User
{
    return User::create(array_merge([
        'email' => 'especialista@sonqomaki.test',
        'password' => 'Una-clave-segura-2026',
        'is_active' => true,
    ], $attributes));
}

function patient(array $attributes = []): Patient
{
    static $sequence = 0;
    $sequence++;

    return Patient::create(array_merge([
        'first_names' => 'Ana',
        'last_names' => 'Quispe',
        'dni' => str_pad((string) $sequence, 8, '0', STR_PAD_LEFT),
        'whatsapp_phone' => '+519'.str_pad((string) $sequence, 8, '0', STR_PAD_LEFT),
        'whatsapp_consented_on' => '2026-07-10',
        'status' => Patient::STATUS_ACTIVE,
    ], $attributes));
}

function exercise(array $attributes = []): Exercise
{
    static $sequence = 0;
    $sequence++;

    return Exercise::create(array_merge([
        'name' => 'Ejercicio '.$sequence,
        'description' => 'Descripción de prueba',
        'duration_seconds' => 90,
        'sets' => 3,
        'repetitions' => 12,
        'material_url' => 'https://example.com/material/'.$sequence,
    ], $attributes));
}
