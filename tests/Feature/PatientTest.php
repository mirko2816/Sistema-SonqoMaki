<?php

use App\Models\Patient;
use App\Modules\Patients\Actions\ArchivePatient;
use App\Modules\Patients\Actions\CreatePatient;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function validPatientData(array $attributes = []): array
{
    return array_merge([
        'first_names' => '  María Elena  ',
        'last_names' => '  Flores Rojas  ',
        'dni' => '01234567',
        'whatsapp_phone' => '987 654-321',
        'whatsapp_consented_on' => '2026-07-15',
        'status' => 'active',
    ], $attributes);
}

it('protege todas las pantallas y operaciones de pacientes con autenticación', function () {
    $patient = patient();

    foreach ([
        ['get', route('patients.index')],
        ['get', route('patients.create')],
        ['get', route('patients.archived')],
        ['post', route('patients.store')],
        ['get', route('patients.show', $patient)],
        ['get', route('patients.edit', $patient)],
        ['put', route('patients.update', $patient)],
        ['patch', route('patients.status', $patient)],
        ['delete', route('patients.destroy', $patient)],
    ] as [$method, $url]) {
        $this->{$method}($url)->assertRedirect(route('login'));
    }
});

it('ofrece una sección separada de solo consulta para pacientes archivados', function () {
    $visible = patient(['first_names' => 'Visible']);
    $archived = patient(['first_names' => 'Archivado']);
    $archived->delete();

    $this->actingAs(specialist())->get(route('patients.archived'))
        ->assertOk()
        ->assertSee($archived->full_name)
        ->assertDontSee($visible->full_name)
        ->assertSee('La restauración es una operación técnica')
        ->assertDontSee('Restaurar');
});

it('permite acceder al listado y marca pacientes como navegación activa', function () {
    $this->actingAs(specialist())->get(route('patients.index'))
        ->assertOk()
        ->assertSee('Pacientes')
        ->assertSee('aria-current="page"', false)
        ->assertSee('Registrar paciente');
});

it('muestra el estado vacío y el estado sin resultados', function () {
    $user = specialist();

    $this->actingAs($user)->get(route('patients.index'))
        ->assertSee('Todavía no hay pacientes');

    patient();
    $this->actingAs($user)->get(route('patients.index', ['search' => 'nadie']))
        ->assertSee('No encontramos coincidencias');
});

it('lista pacientes no archivados con orden predecible y excluye archivados', function () {
    $visible = patient(['first_names' => 'Zulema', 'last_names' => 'Álvarez']);
    $first = patient(['first_names' => 'Ana', 'last_names' => 'Álvarez']);
    $archived = patient(['first_names' => 'Oculto', 'last_names' => 'Paciente']);
    $archived->delete();

    $this->actingAs(specialist())->get(route('patients.index'))
        ->assertSeeInOrder([$first->full_name, $visible->full_name])
        ->assertDontSee($archived->full_name);
});

it('pagina el listado sin cargar todos los pacientes', function () {
    foreach (range(1, 11) as $number) {
        patient(['first_names' => "Paciente {$number}", 'last_names' => 'Prueba']);
    }

    $response = $this->actingAs(specialist())->get(route('patients.index'));
    $response->assertOk();
    expect($response->viewData('patients')->count())->toBe(10)
        ->and($response->viewData('patients')->lastPage())->toBe(2);
});

it('busca por nombres apellidos DNI y teléfono normalizado y conserva filtros', function () {
    $user = specialist();
    $target = patient([
        'first_names' => 'Lucía Fernanda',
        'last_names' => 'Paredes Soto',
        'dni' => '87654321',
        'whatsapp_phone' => '+51987654321',
        'status' => 'inactive',
    ]);

    foreach (['Lucía', 'Paredes', '87654321', '987 654 321'] as $term) {
        $this->actingAs($user)->get(route('patients.index', ['search' => $term]))
            ->assertSee($target->full_name);
    }

    $response = $this->actingAs($user)->get(route('patients.index', ['search' => 'Paredes', 'status' => 'inactive']));
    expect($response->viewData('patients')->appends([])->url(2))->toContain('search=Paredes')->toContain('status=inactive');
});

it('registra un paciente normalizando nombres teléfono y DNI opcional', function () {
    $response = $this->actingAs(specialist())->post(route('patients.store'), validPatientData(['dni' => '']));

    $patient = Patient::sole();
    $response->assertRedirect(route('patients.show', $patient))->assertSessionHas('status');
    expect($patient->first_names)->toBe('María Elena')
        ->and($patient->last_names)->toBe('Flores Rojas')
        ->and($patient->whatsapp_phone)->toBe('+51987654321')
        ->and($patient->dni)->toBeNull()
        ->and($patient->status)->toBe('active');
});

it('permite registrar sin consentimiento y no lo inventa', function () {
    $this->actingAs(specialist())->post(route('patients.store'), validPatientData([
        'dni' => null,
        'whatsapp_consented_on' => null,
    ]))->assertSessionHasNoErrors();

    expect(Patient::sole()->whatsapp_consented_on)->toBeNull();
});

it('rechaza datos inválidos y fechas futuras conservando la entrada', function () {
    $this->actingAs(specialist())->from(route('patients.create'))->post(route('patients.store'), validPatientData([
        'first_names' => '   ',
        'last_names' => '',
        'dni' => '123A',
        'whatsapp_phone' => '555',
        'whatsapp_consented_on' => '2099-01-01',
        'status' => 'arbitrary',
    ]))->assertRedirect(route('patients.create'))
        ->assertSessionHasErrors(['first_names', 'last_names', 'dni', 'whatsapp_phone', 'whatsapp_consented_on', 'status'])
        ->assertSessionHasInput('dni', '123A');

    expect(Patient::count())->toBe(0);
});

it('rechaza DNI y teléfonos duplicados incluso cuando el paciente está archivado', function () {
    $reserved = patient(['dni' => '12345678', 'whatsapp_phone' => '+51987654321']);
    $reserved->delete();

    $this->actingAs(specialist())->post(route('patients.store'), validPatientData(['dni' => '12345678']))
        ->assertSessionHasErrors(['dni', 'whatsapp_phone']);

    expect(Patient::withTrashed()->count())->toBe(1);
});

it('transforma una colisión de unicidad de PostgreSQL en un mensaje comprensible', function () {
    patient(['dni' => '12345678', 'whatsapp_phone' => '+51987654321']);

    try {
        app(CreatePatient::class)->handle(validPatientData([
            'dni' => null,
            'whatsapp_phone' => '+51987654321',
        ]));
        $this->fail('La colisión debía producir un error de validación.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('whatsapp_phone')
            ->and($exception->errors()['whatsapp_phone'][0])->toContain('otro paciente');
    }

    expect(Patient::count())->toBe(1);
});

it('impone formatos estados y unicidad también mediante restricciones PostgreSQL', function () {
    expect(fn () => Patient::create(validPatientData(['first_names' => '', 'whatsapp_phone' => '+51900000001'])))
        ->toThrow(QueryException::class);
    expect(fn () => Patient::create(validPatientData(['dni' => 'ABC', 'whatsapp_phone' => '+51900000002'])))
        ->toThrow(QueryException::class);
    expect(fn () => Patient::create(validPatientData(['dni' => null, 'whatsapp_phone' => '900000003'])))
        ->toThrow(QueryException::class);
    expect(fn () => Patient::create(validPatientData(['dni' => null, 'whatsapp_phone' => '+51900000004', 'status' => 'x'])))
        ->toThrow(QueryException::class);
});

it('muestra el detalle y el estado vacío real de planes', function () {
    $patient = patient();

    $this->actingAs(specialist())->get(route('patients.show', $patient))
        ->assertOk()
        ->assertSee($patient->full_name)
        ->assertSee($patient->whatsapp_phone)
        ->assertSee('Paciente sin planes')
        ->assertDontSee('rutina vigente');
});

it('edita un paciente sin falsos duplicados y bloquea asignación masiva ajena', function () {
    $patient = patient();
    $user = specialist(['email' => 'seguro@sonqomaki.test']);

    $this->actingAs($user)->put(route('patients.update', $patient), validPatientData([
        'dni' => $patient->dni,
        'whatsapp_phone' => $patient->whatsapp_phone,
        'updated_at' => $patient->updated_at->toISOString(),
        'password' => 'hack',
        'email' => 'cambiado@test.com',
        'deleted_at' => now(),
    ]))->assertRedirect(route('patients.show', $patient));

    expect($patient->fresh()->first_names)->toBe('María Elena')
        ->and($patient->fresh()->deleted_at)->toBeNull()
        ->and($user->fresh()->email)->toBe('seguro@sonqomaki.test');
});

it('evita sobrescribir una edición concurrente', function () {
    $patient = patient();
    $oldTimestamp = $patient->updated_at->toISOString();
    DB::table('patients')->where('id', $patient->id)->update([
        'first_names' => 'Cambio externo',
        'updated_at' => now()->addMinute(),
    ]);

    $this->actingAs(specialist())->put(route('patients.update', $patient), validPatientData([
        'dni' => $patient->dni,
        'whatsapp_phone' => $patient->whatsapp_phone,
        'updated_at' => $oldTimestamp,
    ]))->assertSessionHasErrors('updated_at');

    expect($patient->fresh()->first_names)->toBe('Cambio externo');
});

it('exige confirmación al retirar un consentimiento existente', function () {
    $patient = patient();
    $data = validPatientData([
        'dni' => $patient->dni,
        'whatsapp_phone' => $patient->whatsapp_phone,
        'whatsapp_consented_on' => null,
        'updated_at' => $patient->updated_at->toISOString(),
    ]);

    $this->actingAs(specialist())->put(route('patients.update', $patient), $data)
        ->assertSessionHasErrors('consent_removal_confirmed');

    $this->put(route('patients.update', $patient), [...$data, 'consent_removal_confirmed' => '1'])
        ->assertSessionHasNoErrors();

    expect($patient->fresh()->whatsapp_consented_on)->toBeNull();
});

it('cambia entre estados válidos solo mediante una operación mutable', function () {
    $patient = patient();
    $user = specialist();

    $this->actingAs($user)->patch(route('patients.status', $patient), ['status' => 'inactive'])
        ->assertRedirect(route('patients.show', $patient));
    expect($patient->fresh()->status)->toBe('inactive');

    $this->actingAs($user)->patch(route('patients.status', $patient), ['status' => 'invalid'])
        ->assertSessionHasErrors('status');
    $this->actingAs($user)->get(route('patients.status', $patient))->assertMethodNotAllowed();
});

it('archiva de forma lógica, deja inactivo, conserva datos y excluye rutas normales', function () {
    $patient = patient();
    $user = specialist();

    $this->actingAs($user)->delete(route('patients.destroy', $patient), ['archive_confirmed' => '1'])
        ->assertRedirect(route('patients.index'))->assertSessionHas('status');

    $stored = Patient::withTrashed()->findOrFail($patient->id);
    expect($stored->status)->toBe('inactive')->and($stored->deleted_at)->not->toBeNull();
    $this->assertDatabaseCount('patients', 1);
    $this->actingAs($user)->get(route('patients.show', $patient->id))->assertNotFound();
    $this->actingAs($user)->put(route('patients.update', $patient->id), [])->assertNotFound();
});

it('requiere confirmación para archivar y no permite archivo por GET', function () {
    $patient = patient();
    $user = specialist();
    $this->actingAs($user)->delete(route('patients.destroy', $patient), [])
        ->assertSessionHasErrors('archive_confirmed');
    $this->actingAs($user)->get(route('patients.destroy', $patient))->assertOk();
    expect($patient->fresh())->not->toBeNull();
});

it('revierte toda la operación cuando el archivo falla', function () {
    $patient = patient();
    Patient::deleting(function (): void {
        throw new RuntimeException('Fallo simulado');
    });

    expect(fn () => app(ArchivePatient::class)->handle($patient))
        ->toThrow(RuntimeException::class);

    $patient = Patient::findOrFail($patient->id);
    expect($patient->status)->toBe('active')->and($patient->deleted_at)->toBeNull();
});

it('protege las escrituras de pacientes con CSRF', function () {
    $this->app->instance(ValidateCsrfToken::class, new class($this->app, $this->app['encrypter']) extends ValidateCsrfToken
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    });

    $this->actingAs(specialist())->post(route('patients.store'), validPatientData())->assertStatus(419);
    expect(Patient::count())->toBe(0);
});
