<?php

use App\Models\Exercise;
use App\Modules\Exercises\Actions\CreateExercise;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function validExerciseData(array $attributes = []): array
{
    return array_merge([
        'name' => '  Sentadilla asistida  ',
        'description' => '  Mantener la espalda recta.  ',
        'duration_seconds' => '90',
        'sets' => '3',
        'repetitions' => '12',
        'material_url' => '  https://recursos.example.org/ejercicios/sentadilla  ',
    ], $attributes);
}

it('protege todas las pantallas y escrituras de ejercicios con autenticación', function () {
    $exercise = exercise();

    foreach ([
        ['get', route('exercises.index')],
        ['get', route('exercises.create')],
        ['post', route('exercises.store')],
        ['get', route('exercises.show', $exercise)],
        ['get', route('exercises.edit', $exercise)],
        ['put', route('exercises.update', $exercise)],
        ['delete', route('exercises.destroy', $exercise)],
    ] as [$method, $url]) {
        $this->{$method}($url)->assertRedirect(route('login'));
    }
});

it('permite acceder y marca ejercicios como navegación activa sin romper secciones pendientes', function () {
    $this->actingAs(specialist())->get(route('exercises.index'))
        ->assertOk()
        ->assertSee('Biblioteca de ejercicios')
        ->assertSee('aria-current="page"', false)
        ->assertSee('Rutinas')
        ->assertSee('Próximamente');
});

it('muestra estados vacío y sin resultados diferentes', function () {
    $user = specialist();

    $this->actingAs($user)->get(route('exercises.index'))
        ->assertSee('Todavía no hay ejercicios');

    exercise();
    $this->actingAs($user)->get(route('exercises.index', ['search' => 'inexistente']))
        ->assertSee('No encontramos ejercicios');
});

it('lista solo ejercicios activos con orden estable', function () {
    $last = exercise(['name' => 'Zancada']);
    $first = exercise(['name' => 'Abducción']);
    $retired = exercise(['name' => 'Oculto']);
    $retired->delete();

    $this->actingAs(specialist())->get(route('exercises.index'))
        ->assertSeeInOrder([$first->name, $last->name])
        ->assertDontSee($retired->name);
});

it('pagina y conserva la búsqueda al cambiar de página', function () {
    foreach (range(1, 11) as $number) {
        exercise(['name' => "Movilidad {$number}"]);
    }

    $response = $this->actingAs(specialist())->get(route('exercises.index', ['search' => 'Movilidad']));

    expect($response->viewData('exercises')->count())->toBe(10)
        ->and($response->viewData('exercises')->lastPage())->toBe(2)
        ->and($response->viewData('exercises')->url(2))->toContain('search=Movilidad');
});

it('busca por nombre y descripción y trata comodines como texto', function () {
    $user = specialist();
    $target = exercise(['name' => 'Puente glúteo', 'description' => 'Activación de cadera']);
    exercise(['name' => 'Porcentaje 100%', 'description' => null]);

    foreach (['Puente', 'cadera'] as $term) {
        $this->actingAs($user)->get(route('exercises.index', ['search' => $term]))
            ->assertSee($target->name);
    }

    $this->actingAs($user)->get(route('exercises.index', ['search' => '%']))
        ->assertSee('Porcentaje 100%')
        ->assertDontSee($target->name);
});

it('registra un ejercicio mínimo y normaliza opcionales vacíos', function () {
    $response = $this->actingAs(specialist())->post(route('exercises.store'), validExerciseData([
        'name' => '  Respiración diafragmática  ',
        'description' => '   ',
        'duration_seconds' => '',
        'sets' => '',
        'repetitions' => '',
        'material_url' => '',
    ]));

    $exercise = Exercise::sole();
    $response->assertRedirect(route('exercises.show', $exercise))->assertSessionHas('status');
    expect($exercise->name)->toBe('Respiración diafragmática')
        ->and($exercise->description)->toBeNull()
        ->and($exercise->duration_seconds)->toBeNull()
        ->and($exercise->sets)->toBeNull()
        ->and($exercise->repetitions)->toBeNull()
        ->and($exercise->material_url)->toBeNull();
});

it('registra todos los campos permitidos en sus unidades correctas', function () {
    $user = specialist();

    $this->actingAs($user)->post(route('exercises.store'), validExerciseData())
        ->assertSessionHasNoErrors();

    $exercise = Exercise::sole();
    expect($exercise->name)->toBe('Sentadilla asistida')
        ->and($exercise->description)->toBe('Mantener la espalda recta.')
        ->and($exercise->duration_seconds)->toBe(90)
        ->and($exercise->sets)->toBe(3)
        ->and($exercise->repetitions)->toBe(12)
        ->and($exercise->material_url)->toBe('https://recursos.example.org/ejercicios/sentadilla');

    $this->actingAs($user)->get(route('exercises.show', $exercise))
        ->assertSee('1 min 30 s');
});

it('rechaza nombre vacío y números inválidos sin crear registros', function (array $invalid, array $fields) {
    $this->actingAs(specialist())->from(route('exercises.create'))
        ->post(route('exercises.store'), validExerciseData($invalid))
        ->assertRedirect(route('exercises.create'))
        ->assertSessionHasErrors($fields);

    expect(Exercise::count())->toBe(0);
})->with([
    'nombre vacío' => [['name' => '   '], ['name']],
    'ceros' => [['duration_seconds' => 0, 'sets' => 0, 'repetitions' => 0], ['duration_seconds', 'sets', 'repetitions']],
    'negativos' => [['duration_seconds' => -1, 'sets' => -1, 'repetitions' => -1], ['duration_seconds', 'sets', 'repetitions']],
    'decimales' => [['duration_seconds' => 1.5, 'sets' => 2.5, 'repetitions' => 3.5], ['duration_seconds', 'sets', 'repetitions']],
    'fuera del tipo PostgreSQL' => [['duration_seconds' => 2147483648, 'sets' => 32768, 'repetitions' => 32768], ['duration_seconds', 'sets', 'repetitions']],
]);

it('acepta materiales http y https de distintos proveedores', function (string $url) {
    $this->actingAs(specialist())->post(route('exercises.store'), validExerciseData(['material_url' => $url]))
        ->assertSessionHasNoErrors();

    expect(Exercise::sole()->material_url)->toBe($url);
})->with([
    'youtube' => 'https://www.youtube.com/watch?v=abc123',
    'imagen externa' => 'https://cdn.example.net/guia/imagen.webp?version=2',
    'recurso http' => 'http://example.org/material',
]);

it('rechaza URLs inválidas y esquemas inseguros', function (string $url) {
    $this->actingAs(specialist())->post(route('exercises.store'), validExerciseData(['material_url' => $url]))
        ->assertSessionHasErrors('material_url');

    expect(Exercise::count())->toBe(0);
})->with([
    'texto' => 'esto no es una url',
    'javascript' => 'javascript:alert(1)',
    'data' => 'data:text/html;base64,SG9sYQ==',
    'archivo local' => 'file:///etc/passwd',
]);

it('ignora campos ajenos y protege la asignación masiva', function () {
    $user = specialist(['email' => 'seguro@sonqomaki.test']);

    $this->actingAs($user)->post(route('exercises.store'), validExerciseData([
        'deleted_at' => now(),
        'email' => 'cambiado@example.com',
        'is_admin' => true,
    ]))->assertSessionHasNoErrors();

    expect(Exercise::sole()->deleted_at)->toBeNull()
        ->and($user->fresh()->email)->toBe('seguro@sonqomaki.test');
});

it('convierte un conflicto PostgreSQL en un error comprensible y revierte la escritura', function () {
    try {
        app(CreateExercise::class)->handle(validExerciseData(['name' => '']));
        $this->fail('La restricción debía rechazar el registro.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('name')
            ->and($exception->errors()['name'][0])->toContain('No se pudo registrar');
    }

    expect(Exercise::count())->toBe(0);
});

it('impone texto no vacío y números positivos en PostgreSQL', function () {
    expect(fn () => Exercise::create(validExerciseData(['name' => ''])))->toThrow(QueryException::class);
    expect(fn () => Exercise::create(validExerciseData(['name' => 'Válido', 'duration_seconds' => 0])))->toThrow(QueryException::class);
    expect(fn () => Exercise::create(validExerciseData(['name' => 'Válido', 'sets' => 0])))->toThrow(QueryException::class);
    expect(fn () => Exercise::create(validExerciseData(['name' => 'Válido', 'repetitions' => 0])))->toThrow(QueryException::class);
});

it('consulta el detalle y renderiza el enlace externo de forma segura', function () {
    $exercise = exercise([
        'name' => '<script>alert(1)</script>',
        'material_url' => 'https://example.com/recurso?x=uno&y=dos',
    ]);

    $this->actingAs(specialist())->get(route('exercises.show', $exercise))
        ->assertOk()
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false);
});

it('edita con las mismas normalizaciones y permite retirar opcionales', function () {
    $exercise = exercise();

    $this->actingAs(specialist())->put(route('exercises.update', $exercise), validExerciseData([
        'name' => '  Movilidad cervical  ',
        'description' => '',
        'duration_seconds' => '',
        'sets' => '',
        'repetitions' => '',
        'material_url' => '',
        'updated_at' => $exercise->updated_at->toISOString(),
    ]))->assertRedirect(route('exercises.show', $exercise))->assertSessionHas('status');

    $exercise->refresh();
    expect($exercise->name)->toBe('Movilidad cervical')
        ->and($exercise->description)->toBeNull()
        ->and($exercise->duration_seconds)->toBeNull()
        ->and($exercise->sets)->toBeNull()
        ->and($exercise->repetitions)->toBeNull()
        ->and($exercise->material_url)->toBeNull();
});

it('evita cambios parciales y sobrescritura concurrente al editar', function () {
    $exercise = exercise();
    $oldTimestamp = $exercise->updated_at->toISOString();
    DB::table('exercises')->where('id', $exercise->id)->update([
        'name' => 'Cambio externo',
        'updated_at' => now()->addMinute(),
    ]);

    $this->actingAs(specialist())->put(route('exercises.update', $exercise), validExerciseData([
        'name' => 'Cambio del formulario',
        'updated_at' => $oldTimestamp,
    ]))->assertSessionHasErrors('updated_at');

    expect($exercise->fresh()->name)->toBe('Cambio externo')
        ->and($exercise->fresh()->sets)->toBe(3);
});

it('retira de forma lógica con confirmación y conserva físicamente el registro', function () {
    $exercise = exercise();
    $user = specialist();

    $this->actingAs($user)->delete(route('exercises.destroy', $exercise), [])
        ->assertSessionHasErrors('retirement_confirmed');
    expect($exercise->fresh()->deleted_at)->toBeNull();

    $this->delete(route('exercises.destroy', $exercise), ['retirement_confirmed' => '1'])
        ->assertRedirect(route('exercises.index'))->assertSessionHas('status');

    expect(Exercise::withTrashed()->findOrFail($exercise->id)->deleted_at)->not->toBeNull();
    $this->assertDatabaseCount('exercises', 1);
    $this->get(route('exercises.index'))->assertDontSee($exercise->name);
    $this->get(route('exercises.edit', $exercise->id))->assertNotFound();
    $this->put(route('exercises.update', $exercise->id), [])->assertNotFound();
});

it('no elimina mediante GET y una segunda solicitud es idempotente', function () {
    $exercise = exercise();
    $user = specialist();

    $this->actingAs($user)->get(route('exercises.destroy', $exercise))->assertOk();
    expect($exercise->fresh()->deleted_at)->toBeNull();

    $this->delete(route('exercises.destroy', $exercise), ['retirement_confirmed' => '1'])->assertSessionHas('status');
    $this->delete(route('exercises.destroy', $exercise->id), ['retirement_confirmed' => '1'])
        ->assertRedirect(route('exercises.index'))
        ->assertSessionHas('status', 'El ejercicio ya había sido retirado; no se realizó ningún cambio.');

    expect(Exercise::withTrashed()->count())->toBe(1);
});

it('no crea tablas ni dependencias de rutinas futuras', function () {
    expect(Schema::hasTable('exercises'))->toBeTrue()
        ->and(Schema::hasTable('routines'))->toBeFalse()
        ->and(Schema::hasTable('routine_exercises'))->toBeFalse()
        ->and(Schema::hasTable('routine_templates'))->toBeFalse()
        ->and(Schema::hasTable('routine_template_exercises'))->toBeFalse();
});

it('protege todas las escrituras de ejercicios con CSRF', function () {
    $exercise = exercise();
    $this->app->instance(ValidateCsrfToken::class, new class($this->app, $this->app['encrypter']) extends ValidateCsrfToken
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    });

    $this->actingAs(specialist())->post(route('exercises.store'), validExerciseData())->assertStatus(419);
    $this->put(route('exercises.update', $exercise), validExerciseData(['updated_at' => $exercise->updated_at->toISOString()]))->assertStatus(419);
    $this->delete(route('exercises.destroy', $exercise), ['retirement_confirmed' => '1'])->assertStatus(419);
});
