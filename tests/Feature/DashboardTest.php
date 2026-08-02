<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('redirige a un visitante del dashboard al inicio de sesión', function () {
    $this->get('/dashboard')
        ->assertRedirect(route('login'));
});

it('permite que el especialista acceda al dashboard con el layout autenticado', function () {
    $user = specialist();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertViewIs('dashboard')
        ->assertSee('<title>Dashboard ·', false)
        ->assertSee('Navegación principal')
        ->assertSee('Cerrar sesión')
        ->assertSee($user->email);
});

it('muestra los módulos disponibles y conserva futuras secciones sin enlaces falsos', function () {
    $response = $this->actingAs(specialist())->get('/dashboard');

    $response
        ->assertOk()
        ->assertSee('aria-current="page"', false)
        ->assertSeeInOrder([
            'Dashboard',
            'Pacientes',
            'Ejercicios',
            'Rutinas',
            'Planes',
            'Recordatorios',
            'Historial de envíos',
        ])
        ->assertSee('Próximamente')
        ->assertDontSee('href="#"', false)
        ->assertSee('href="'.route('patients.index').'"', false)
        ->assertSee('href="'.route('exercises.index').'"', false)
        ->assertSee('href="'.route('plans.index').'"', false);

    expect(app('router')->getRoutes()->getByName('patients.index'))->not->toBeNull();
    expect(app('router')->getRoutes()->getByName('exercises.index'))->not->toBeNull();

    expect(app('router')->getRoutes()->getByName('plans.index'))->not->toBeNull();
    expect(app('router')->getRoutes()->getByName('reminders.index'))->not->toBeNull();
    expect(app('router')->getRoutes()->getByName('routines.index'))->toBeNull();
});

it('muestra un estado vacío real con la estructura futura de planes activos', function () {
    $this->actingAs(specialist())
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Planes activos')
        ->assertSee('Paciente')
        ->assertSee('Teléfono')
        ->assertSee('Plan')
        ->assertSee('Estado')
        ->assertSee('Recordatorios')
        ->assertSee('Todavía no existen planes activos')
        ->assertSee('Los planes válidos que actives aparecerán aquí');
});

it('consulta planes, pacientes y recordatorios sin consultas por cada fila', function () {
    $queries = [];

    DB::listen(function ($query) use (&$queries) {
        $queries[] = $query->sql;
    });

    $this->actingAs(specialist())->get('/dashboard')->assertOk();

    expect(collect($queries)->filter(fn ($query) => str_contains($query, 'plans'))->count())->toBe(1);
    expect(collect($queries)->filter(fn ($query) => str_contains($query, 'patients'))->count())->toBeLessThanOrEqual(1);
    expect(collect($queries)->filter(fn ($query) => str_contains($query, 'reminder_configurations'))->count())->toBeLessThanOrEqual(1);
});

it('mantiene el cierre de sesión seguro desde el dashboard', function () {
    $this->actingAs(specialist())
        ->get('/dashboard')
        ->assertSee('action="'.route('logout').'"', false)
        ->assertSee('method="POST"', false);

    $this->post('/cerrar-sesion')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
