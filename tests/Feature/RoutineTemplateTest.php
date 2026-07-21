<?php

use App\Models\Exercise;
use App\Models\RoutineTemplate;
use App\Models\RoutineTemplateExercise;
use App\Modules\RoutineTemplates\Actions\CreateRoutineTemplate;
use App\Modules\RoutineTemplates\Actions\UpdateRoutineTemplate;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function templatePayload(array $attributes = [], mixed $items = null): array
{
    $items ??= [];

    return array_merge(['name' => '  Rutina de movilidad  ', 'exercises' => $items], $attributes);
}

function copiedExercisePayload(Exercise $source, int $position = 1, array $attributes = []): array
{
    return array_merge([
        'source_exercise_id' => $source->id,
        'position' => $position,
        'name' => $source->name,
        'description' => $source->description,
        'duration_seconds' => $source->duration_seconds,
        'sets' => $source->sets,
        'repetitions' => $source->repetitions,
        'material_url' => $source->material_url,
    ], $attributes);
}

it('protege todas las rutas y escrituras de plantillas con autenticación', function () {
    $template = routineTemplate();
    foreach ([
        ['get', route('routine-templates.index')], ['get', route('routine-templates.create')],
        ['get', route('routine-templates.exercise-search')], ['post', route('routine-templates.store')],
        ['get', route('routine-templates.show', $template)], ['get', route('routine-templates.edit', $template)],
        ['put', route('routine-templates.update', $template)], ['delete', route('routine-templates.destroy', $template)],
    ] as [$method, $url]) {
        $this->{$method}($url)->assertRedirect(route('login'));
    }
});

it('muestra estados vacío y sin resultados y activa la navegación', function () {
    $user = specialist();
    $this->actingAs($user)->get(route('routine-templates.index'))
        ->assertOk()->assertSee('Todavía no hay plantillas')->assertSee('aria-current="page"', false);
    routineTemplate();
    $this->get(route('routine-templates.index', ['search' => 'nada']))->assertSee('No encontramos plantillas');
});

it('lista activas con cantidades, búsqueda, orden y paginación sin cargar ejercicios', function () {
    $user = specialist();
    routineTemplate(['name' => 'Zeta'], [['name' => 'Uno']]);
    routineTemplate(['name' => 'Abdominal'], [['name' => 'Uno'], ['name' => 'Dos']]);
    $archived = routineTemplate(['name' => 'Oculta', 'status' => RoutineTemplate::STATUS_ARCHIVED]);
    $archived->delete();
    foreach (range(1, 9) as $number) {
        routineTemplate(['name' => "Movilidad {$number}"]);
    }

    $response = $this->actingAs($user)->get(route('routine-templates.index'));
    $response->assertSeeInOrder(['Abdominal', 'Movilidad 1'])->assertSee('2 ejercicios configurados')->assertDontSee('Oculta');
    expect($response->viewData('templates')->count())->toBe(10)
        ->and($response->viewData('templates')->lastPage())->toBe(2)
        ->and($response->viewData('templates')->first()->relationLoaded('exercises'))->toBeFalse();
    $this->get(route('routine-templates.index', ['search' => '100%']))->assertDontSee('Abdominal');
    $this->get(route('routine-templates.index', ['search' => 'Abdom']))->assertSee('Abdominal');
});

it('busca solo ejercicios activos y limita los resultados', function () {
    $user = specialist();
    foreach (range(1, 11) as $number) {
        exercise(['name' => "Movilidad {$number}"]);
    }
    $retired = exercise(['name' => 'Movilidad retirada']);
    $retired->delete();
    $response = $this->actingAs($user)->getJson(route('routine-templates.exercise-search', ['search' => 'Movilidad']));
    $response->assertOk()->assertJsonCount(10, 'data')->assertJsonMissing(['id' => $retired->id]);
});

it('crea una plantilla vacía válida y normaliza el nombre', function () {
    $response = $this->actingAs(specialist())->post(route('routine-templates.store'), templatePayload());
    $template = RoutineTemplate::sole();
    $response->assertRedirect(route('routine-templates.show', $template))->assertSessionHas('status');
    expect($template->name)->toBe('Rutina de movilidad')->and($template->exercises)->toHaveCount(0);
});

it('rechaza nombre vacío, URLs inseguras, números y posiciones manipuladas', function () {
    $source = exercise();
    $payload = templatePayload(['name' => '   '], [copiedExercisePayload($source, 2, [
        'duration_seconds' => 0, 'sets' => -1, 'repetitions' => 1.5, 'material_url' => 'javascript:alert(1)',
    ])]);
    $this->actingAs(specialist())->post(route('routine-templates.store'), $payload)
        ->assertSessionHasErrors(['name', 'exercises', 'exercises.0.duration_seconds', 'exercises.0.sets', 'exercises.0.repetitions', 'exercises.0.material_url']);
    expect(RoutineTemplate::count())->toBe(0);
});

it('rechaza estructuras manipuladas sin producir errores internos', function (array|string $exercises, string $error) {
    $this->actingAs(specialist())->post(route('routine-templates.store'), templatePayload([], $exercises))
        ->assertSessionHasErrors($error);

    expect(RoutineTemplate::count())->toBe(0);
})->with([
    'colección escalar' => ['manipulada', 'exercises'],
    'elemento escalar' => [['manipulado'], 'exercises.0'],
]);

it('rechaza ejercicios inexistentes y archivados en nuevas copias', function () {
    $archived = exercise();
    $archived->delete();
    $user = specialist();
    foreach ([$archived->id, 999999] as $id) {
        $this->actingAs($user)->post(route('routine-templates.store'), templatePayload([], [[
            'source_exercise_id' => $id, 'position' => 1, 'name' => 'Manipulado',
        ]]))->assertSessionHasErrors('exercises.0.source_exercise_id');
    }
    expect(RoutineTemplate::count())->toBe(0);
});

it('copia y configura todos los datos con posiciones coherentes y permite repetir origen', function () {
    $source = exercise(['name' => 'Original', 'description' => 'Base']);
    $items = [
        copiedExercisePayload($source, 1, ['name' => 'Copia A', 'sets' => 5]),
        copiedExercisePayload($source, 2, ['name' => 'Copia B', 'description' => null, 'material_url' => null]),
    ];
    $this->actingAs(specialist())->post(route('routine-templates.store'), templatePayload([], $items))->assertSessionHasNoErrors();
    $copies = RoutineTemplate::sole()->exercises;
    expect($copies)->toHaveCount(2)->and($copies->pluck('position')->all())->toBe([1, 2])
        ->and($copies[0]->source_exercise_id)->toBe($source->id)->and($copies[0]->name)->toBe('Copia A')
        ->and($copies[0]->sets)->toBe(5)->and($copies[1]->material_url)->toBeNull();
});

it('acepta posiciones enviadas como texto por formularios HTML', function () {
    $source = exercise();
    $payload = templatePayload([], [copiedExercisePayload($source, 1, ['position' => '1'])]);
    $this->actingAs(specialist())->post(route('routine-templates.store'), $payload)->assertSessionHasNoErrors();
    expect(RoutineTemplateExercise::sole()->position)->toBe(1);
});

it('revierte toda la creación si una fila falla', function () {
    $source = exercise();
    $data = templatePayload([], [copiedExercisePayload($source), copiedExercisePayload($source, 2, ['sets' => 0])]);
    expect(fn () => app(CreateRoutineTemplate::class)->handle($data))->toThrow(ValidationException::class);
    expect(RoutineTemplate::count())->toBe(0)->and(RoutineTemplateExercise::count())->toBe(0);
});

it('mantiene copias independientes al editar o archivar el origen y permite referencia nula', function () {
    $source = exercise(['name' => 'Nombre original']);
    $this->actingAs(specialist())->post(route('routine-templates.store'), templatePayload([], [copiedExercisePayload($source)]));
    $copy = RoutineTemplateExercise::sole();
    $source->update(['name' => 'Nombre cambiado', 'sets' => 99]);
    $source->delete();
    expect($copy->fresh()->name)->toBe('Nombre original')->and($copy->fresh()->sets)->toBe(3);
    DB::table('routine_template_exercises')->where('id', $copy->id)->update(['source_exercise_id' => null]);
    $this->get(route('routine-templates.show', $copy->template))->assertOk()->assertSee('Nombre original');
});

it('edita metadatos, conserva identidad, agrega, retira, modifica y reordena copias', function () {
    $sourceA = exercise(['name' => 'A']);
    $sourceB = exercise(['name' => 'B']);
    $sourceC = exercise(['name' => 'C']);
    $template = routineTemplate([], [copiedExercisePayload($sourceA), copiedExercisePayload($sourceB, 2)]);
    $first = $template->exercises[0];
    $second = $template->exercises[1];
    $payload = templatePayload(['name' => 'Editada', 'updated_at' => $template->updated_at->toISOString()], [
        [...copiedExercisePayload($sourceB, 1, ['name' => 'B ajustada', 'sets' => 7]), 'id' => $second->id],
        copiedExercisePayload($sourceC, 2),
    ]);
    $this->actingAs(specialist())->put(route('routine-templates.update', $template), $payload)->assertSessionHasNoErrors();
    $template->refresh();
    $copies = $template->exercises;
    expect($template->name)->toBe('Editada')->and($copies->pluck('position')->all())->toBe([1, 2])
        ->and($copies[0]->id)->toBe($second->id)->and($copies[0]->sets)->toBe(7)
        ->and(RoutineTemplateExercise::withTrashed()->find($first->id)->trashed())->toBeTrue();
});

it('impide identificadores ajenos, sobrescritura concurrente y revierte actualizaciones fallidas', function () {
    $source = exercise();
    $template = routineTemplate([], [copiedExercisePayload($source)]);
    $other = routineTemplate([], [copiedExercisePayload($source)]);
    $bad = templatePayload(['updated_at' => $template->updated_at->toISOString()], [[
        ...copiedExercisePayload($source), 'id' => $other->exercises[0]->id,
    ]]);
    $this->actingAs(specialist())->put(route('routine-templates.update', $template), $bad)->assertSessionHasErrors('exercises.0.id');

    $old = $template->updated_at->toISOString();
    DB::table('routine_templates')->where('id', $template->id)->update(['name' => 'Cambio externo', 'updated_at' => now()->addMinute()]);
    $this->put(route('routine-templates.update', $template), templatePayload(['updated_at' => $old]))->assertSessionHasErrors('updated_at');
    expect($template->fresh()->name)->toBe('Cambio externo');

    $fresh = $template->fresh();
    $direct = templatePayload(['name' => 'No persistir', 'updated_at' => $fresh->updated_at->toISOString()], [[
        ...copiedExercisePayload($source), 'id' => $fresh->exercises[0]->id, 'sets' => 0,
    ]]);
    expect(fn () => app(UpdateRoutineTemplate::class)->handle($fresh, $direct))->toThrow(ValidationException::class);
    expect($fresh->fresh()->name)->toBe('Cambio externo')->and($fresh->exercises[0]->fresh()->sets)->toBe(3);
});

it('impone integridad PostgreSQL para textos, valores y posición única', function () {
    $template = routineTemplate();
    expect(fn () => DB::transaction(fn () => RoutineTemplate::create(['name' => '', 'status' => 'active'])))->toThrow(QueryException::class);
    expect(fn () => DB::transaction(fn () => routineTemplate(['status' => 'otro'])))->toThrow(QueryException::class);
    $template->exercises()->create(['position' => 1, 'name' => 'Uno']);
    expect(fn () => DB::transaction(fn () => $template->exercises()->create(['position' => 1, 'name' => 'Dos'])))->toThrow(QueryException::class);
    expect(fn () => DB::transaction(fn () => $template->exercises()->create(['position' => 2, 'name' => 'Dos', 'sets' => 0])))->toThrow(QueryException::class);
});

it('archiva lógicamente sin borrar ejercicios y bloquea rutas normales', function () {
    $template = routineTemplate([], [['name' => 'Conservado']]);
    $user = specialist();
    $this->actingAs($user)->delete(route('routine-templates.destroy', $template))->assertSessionHasErrors('archive_confirmed');
    $this->delete(route('routine-templates.destroy', $template), ['archive_confirmed' => '1'])->assertSessionHas('status');
    $archived = RoutineTemplate::withTrashed()->findOrFail($template->id);
    expect($archived->status)->toBe(RoutineTemplate::STATUS_ARCHIVED)->and($archived->trashed())->toBeTrue()
        ->and(RoutineTemplateExercise::count())->toBe(1);
    $this->get(route('routine-templates.index'))->assertDontSee($template->name);
    $this->get(route('routine-templates.edit', $template->id))->assertNotFound();
    $this->put(route('routine-templates.update', $template->id), [])->assertNotFound();
});

it('no archiva por GET y una repetición es idempotente', function () {
    $template = routineTemplate();
    $user = specialist();
    $this->actingAs($user)->get(route('routine-templates.destroy', $template))->assertOk();
    expect($template->fresh()->deleted_at)->toBeNull();
    $this->delete(route('routine-templates.destroy', $template), ['archive_confirmed' => 1]);
    $this->delete(route('routine-templates.destroy', $template->id), ['archive_confirmed' => 1])
        ->assertSessionHas('status', 'La plantilla ya estaba archivada; no se realizó ningún cambio.');
    expect(RoutineTemplate::withTrashed()->count())->toBe(1);
});

it('protege las escrituras con CSRF', function () {
    $template = routineTemplate();
    $this->app->instance(ValidateCsrfToken::class, new class($this->app, $this->app['encrypter']) extends ValidateCsrfToken
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    });
    $this->actingAs(specialist())->post(route('routine-templates.store'), templatePayload())->assertStatus(419);
    $this->put(route('routine-templates.update', $template), [])->assertStatus(419);
    $this->delete(route('routine-templates.destroy', $template), ['archive_confirmed' => 1])->assertStatus(419);
});

it('no introduce planes, fechas, pacientes ni módulos fuera del alcance', function () {
    expect(DB::getSchemaBuilder()->hasTable('plans'))->toBeFalse()
        ->and(DB::getSchemaBuilder()->hasColumn('routine_templates', 'starts_on'))->toBeFalse()
        ->and(DB::getSchemaBuilder()->hasColumn('routine_templates', 'patient_id'))->toBeFalse();
});
