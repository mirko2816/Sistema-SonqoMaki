<?php

use App\Models\Exercise;
use App\Models\Patient;
use App\Models\Plan;
use App\Models\Routine;
use App\Models\RoutineTemplate;
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

function routineTemplate(array $attributes = [], array $exercises = []): RoutineTemplate
{
    static $sequence = 0;
    $sequence++;
    $template = RoutineTemplate::create(array_merge([
        'name' => 'Rutina de prueba '.$sequence,
        'status' => RoutineTemplate::STATUS_ACTIVE,
    ], $attributes));

    foreach ($exercises as $index => $exerciseAttributes) {
        $template->exercises()->create(array_merge([
            'position' => $index + 1,
            'name' => 'Copia '.($index + 1),
        ], $exerciseAttributes));
    }

    return $template;
}

function plan(array $attributes = []): Plan
{
    return Plan::create(array_merge([
        'patient_id' => patient()->id,
        'name' => 'Plan de prueba',
        'starts_on' => '2026-08-01',
        'ends_on' => '2026-08-07',
        'status' => Plan::STATUS_PAUSED,
    ], $attributes));
}

function assignedRoutine(Plan $plan, array $attributes = [], array $exercises = []): Routine
{
    $routine = $plan->routines()->create(array_merge([
        'name' => 'Rutina asignada',
        'starts_on' => $plan->starts_on,
        'ends_on' => $plan->ends_on,
    ], $attributes));
    foreach ($exercises as $index => $values) {
        $routine->exercises()->create(array_merge(['position' => $index + 1, 'name' => 'Ejercicio '.($index + 1)], $values));
    }

    return $routine;
}
