<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirige la raíz pública al inicio de sesión', function () {
    $this->get('/')->assertRedirect(route('login'));
});

it('redirige la raíz autenticada al dashboard', function () {
    $this->actingAs(specialist())
        ->get('/')
        ->assertRedirect(route('dashboard'));
});
