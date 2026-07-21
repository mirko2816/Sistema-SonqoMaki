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

it('muestra pacientes y ejercicios disponibles y las secciones futuras sin enlaces falsos', function () {
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
        ->assertDontSee('href="/planes"', false);

    expect(app('router')->getRoutes()->getByName('patients.index'))->not->toBeNull();
    expect(app('router')->getRoutes()->getByName('exercises.index'))->not->toBeNull();

    foreach (['routines.index', 'plans.index', 'reminders.index'] as $routeName) {
        expect(app('router')->getRoutes()->getByName($routeName))->toBeNull();
    }
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
        ->assertSee('Cuando se incorporen pacientes y se activen sus planes');
});

it('renderiza el dashboard sin consultar tablas de módulos futuros', function () {
    $queries = [];

    DB::listen(function ($query) use (&$queries) {
        $queries[] = $query->sql;
    });

    $this->actingAs(specialist())->get('/dashboard')->assertOk();

    expect($queries)
        ->each(fn ($query) => $query
            ->not->toContain('patients')
            ->not->toContain('plans')
            ->not->toContain('reminders'));
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
