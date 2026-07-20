<?php

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
