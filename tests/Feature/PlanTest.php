<?php

use App\Models\Patient;
use App\Models\Plan;
use App\Models\PublicLink;
use App\Modules\Plans\Actions\DuplicatePlan;
use App\Modules\Plans\Actions\FinishExpiredPlans;
use App\Modules\Plans\Support\PlanActivationValidator;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;

uses(RefreshDatabase::class);

beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-22 10:00', 'America/Lima'));
});
afterEach(function () {
    CarbonImmutable::setTestNow();
});

function validPlanData(Patient $patient, array $overrides = []): array
{
    return array_merge(['patient_id' => $patient->id, 'name' => ' Plan de rodilla ', 'starts_on' => '2026-08-01', 'ends_on' => '2026-08-07'], $overrides);
}

function routinePayload($source, array $overrides = []): array
{
    return array_merge([
        'name' => 'Fase inicial', 'starts_on' => '2026-08-01', 'ends_on' => '2026-08-07',
        'exercises' => [[
            'source_exercise_id' => $source->id, 'position' => 1, 'name' => $source->name,
            'description' => $source->description, 'duration_seconds' => $source->duration_seconds,
            'sets' => $source->sets, 'repetitions' => $source->repetitions, 'material_url' => $source->material_url,
        ]],
    ], $overrides);
}

it('protege todas las rutas administrativas de planes y cambios de estado', function () {
    $plan = plan();
    foreach ([['get', route('plans.index')], ['get', route('plans.show', $plan)], ['get', route('plans.create')], ['patch', route('plans.status', $plan)], ['post', route('plans.duplicate', $plan)]] as $route) {
        $this->{$route[0]}($route[1])->assertRedirect(route('login'));
    }
    $this->get(route('plans.status', $plan))->assertMethodNotAllowed();
});

it('crea un plan normalizado en pausa y permite planes superpuestos', function () {
    $patient = patient();
    $this->actingAs(specialist())->post(route('plans.store'), validPlanData($patient))->assertRedirect();
    $this->actingAs(auth()->user())->post(route('plans.store'), validPlanData($patient, ['name' => 'Segundo plan']))->assertRedirect();
    expect(Plan::count())->toBe(2);
    $this->assertDatabaseHas('plans', ['patient_id' => $patient->id, 'name' => 'Plan de rodilla', 'status' => Plan::STATUS_PAUSED]);
});

it('rechaza fechas inválidas y pacientes inactivos o archivados', function () {
    $user = specialist();
    $inactive = patient(['status' => Patient::STATUS_INACTIVE]);
    $this->actingAs($user)->post(route('plans.store'), validPlanData($inactive))->assertSessionHasErrors('patient_id');
    $archived = patient();
    $archived->delete();
    $this->post(route('plans.store'), validPlanData($archived))->assertSessionHasErrors('patient_id');
    $this->post(route('plans.store'), validPlanData(patient(), ['starts_on' => '2026-08-08', 'ends_on' => '2026-08-01']))->assertSessionHasErrors('ends_on');
    expect(Plan::count())->toBe(0);
});

it('edita sin permitir reasignación masiva ni sobrescritura concurrente', function () {
    $plan = plan();
    $other = patient();
    $this->actingAs(specialist())->put(route('plans.update', $plan), ['patient_id' => $other->id, 'name' => 'Manipulado', 'starts_on' => '2026-08-01', 'ends_on' => '2026-08-08', 'updated_at' => $plan->updated_at->toISOString()])->assertSessionHasErrors('patient_id');
    expect($plan->fresh()->patient_id)->not->toBe($other->id);
    $this->put(route('plans.update', $plan), ['name' => 'Correcto', 'starts_on' => '2026-08-01', 'ends_on' => '2026-08-08', 'updated_at' => $plan->updated_at->subSecond()->toISOString()])->assertSessionHasErrors('updated_at');
});

it('crea una rutina dentro del rango y copia el ejercicio independientemente', function () {
    $plan = plan();
    $source = exercise(['name' => 'Sentadilla original']);
    $this->actingAs(specialist())->post(route('plans.routines.store', $plan), routinePayload($source))->assertRedirect(route('plans.show', $plan));
    $copy = $plan->routines()->first()->exercises()->first();
    $source->update(['name' => 'Nombre nuevo']);
    $source->delete();
    expect($copy->fresh()->name)->toBe('Sentadilla original')->and($copy->sourceExercise()->withTrashed()->exists())->toBeTrue();
});

it('rechaza rutinas fuera del rango y superpuestas incluso en PostgreSQL', function () {
    $plan = plan();
    $source = exercise();
    $this->actingAs(specialist())->post(route('plans.routines.store', $plan), routinePayload($source, ['starts_on' => '2026-07-31']))->assertSessionHasErrors('starts_on');
    assignedRoutine($plan, ['starts_on' => '2026-08-01', 'ends_on' => '2026-08-04']);
    expect(fn () => $plan->routines()->create(['name' => 'Solapada', 'starts_on' => '2026-08-04', 'ends_on' => '2026-08-06']))->toThrow(QueryException::class);
});

it('detecta todos los problemas de cobertura y acepta cobertura contigua completa', function () {
    $plan = plan();
    assignedRoutine($plan, ['name' => 'Uno', 'starts_on' => '2026-08-01', 'ends_on' => '2026-08-02']);
    assignedRoutine($plan, ['name' => 'Dos', 'starts_on' => '2026-08-04', 'ends_on' => '2026-08-07']);
    $problems = app(PlanActivationValidator::class)->problems($plan);
    expect($problems)->toContain('Las rutinas dejan uno o más días sin cobertura.')->and(collect($problems)->filter(fn ($p) => str_contains($p, 'no tiene ejercicios'))->count())->toBe(2);
    $plan->routines()->forceDelete();
    assignedRoutine($plan, ['starts_on' => '2026-08-01', 'ends_on' => '2026-08-03'], [['name' => 'A']]);
    assignedRoutine($plan, ['starts_on' => '2026-08-04', 'ends_on' => '2026-08-07'], [['name' => 'B']]);
    expect(app(PlanActivationValidator::class)->problems($plan->fresh()))->toBe([]);
});

it('calcula la rutina vigente usando la fecha local de America Lima', function () {
    $plan = plan(['starts_on' => '2026-07-20', 'ends_on' => '2026-07-25']);
    $current = assignedRoutine($plan, ['name' => 'Vigente', 'starts_on' => '2026-07-22', 'ends_on' => '2026-07-23'], [['name' => 'A']]);
    $this->actingAs(specialist())->get(route('plans.show', $plan))->assertOk()->assertSee('Vigente hoy')->assertViewHas('currentRoutine', fn ($routine) => $routine->is($current));
});

it('copia una plantilla completa conservando orden e independencia', function () {
    $plan = plan();
    $source = exercise();
    $template = routineTemplate([], [['source_exercise_id' => $source->id, 'name' => 'Primero'], ['source_exercise_id' => $source->id, 'name' => 'Segundo']]);
    $this->actingAs(specialist())->post(route('plans.routines.copy', $plan), ['routine_template_id' => $template->id, 'starts_on' => '2026-08-01', 'ends_on' => '2026-08-07'])->assertRedirect();
    $routine = $plan->routines()->first();
    expect($routine->exercises->pluck('name')->all())->toBe(['Primero', 'Segundo']);
    $template->exercises()->first()->update(['name' => 'Cambiado']);
    $template->delete();
    expect($routine->exercises()->first()->name)->toBe('Primero');
});

it('activa únicamente un plan completo y genera un solo enlace seguro cifrado', function () {
    $plan = plan();
    assignedRoutine($plan, [], [['name' => 'Copia']]);
    $this->actingAs(specialist())->patch(route('plans.status', $plan), ['status' => Plan::STATUS_ACTIVE])->assertRedirect();
    $link = PublicLink::first();
    $token = Crypt::decryptString($link->token_ciphertext);
    expect($plan->fresh()->status)->toBe(Plan::STATUS_ACTIVE)->and(hash('sha256', $token))->toBe($link->token_hash)->and($link->token_ciphertext)->not->toContain($token);
    $this->patch(route('plans.status', $plan), ['status' => Plan::STATUS_PAUSED]);
    $this->patch(route('plans.status', $plan), ['status' => Plan::STATUS_ACTIVE]);
    expect(PublicLink::count())->toBe(1);
});

it('impide reasignar un enlace público a otro plan en PostgreSQL', function () {
    $first = plan();
    $second = plan();
    $link = $first->publicLinks()->create(['token_hash' => str_repeat('b', 64), 'token_ciphertext' => Crypt::encryptString('token'), 'token_prefix' => 'prefijo']);

    expect(fn () => DB::table('public_links')->where('id', $link->id)->update(['plan_id' => $second->id]))
        ->toThrow(QueryException::class);
});

it('bloquea activación incompleta y transiciones desde finalizado', function () {
    $plan = plan();
    $this->actingAs(specialist())->patch(route('plans.status', $plan), ['status' => Plan::STATUS_ACTIVE])->assertSessionHasErrors('status');
    $plan->update(['status' => Plan::STATUS_FINISHED]);
    $this->patch(route('plans.status', $plan), ['status' => Plan::STATUS_ACTIVE])->assertSessionHasErrors('status');
    expect($plan->fresh()->status)->toBe(Plan::STATUS_FINISHED);
});

it('duplica para el mismo u otro paciente sin enlace ni dependencia del origen', function () {
    $source = plan();
    assignedRoutine($source, [], [['name' => 'Original']]);
    $source->publicLinks()->create(['token_hash' => str_repeat('a', 64), 'token_ciphertext' => Crypt::encryptString('secreto'), 'token_prefix' => 'prefijo']);
    $target = patient();
    $copy = app(DuplicatePlan::class)->handle($source, ['patient_id' => $target->id, 'name' => 'Copia']);
    expect($copy->status)->toBe(Plan::STATUS_PAUSED)->and($copy->patient_id)->toBe($target->id)->and($copy->routines->first()->exercises->first()->name)->toBe('Original')->and($copy->publicLinks()->count())->toBe(0);
    $copy->routines->first()->exercises->first()->update(['name' => 'Independiente']);
    expect($source->routines()->first()->exercises()->first()->name)->toBe('Original');
    expect(app(DuplicatePlan::class)->handle($source, ['patient_id' => $source->patient_id, 'name' => 'Mismo paciente'])->patient_id)->toBe($source->patient_id);
});

it('finaliza solamente planes activos vencidos y es idempotente', function () {
    $expired = plan(['starts_on' => '2026-07-01', 'ends_on' => '2026-07-21', 'status' => Plan::STATUS_ACTIVE]);
    $future = plan(['starts_on' => '2026-07-22', 'ends_on' => '2026-07-23', 'status' => Plan::STATUS_ACTIVE]);
    $paused = plan(['starts_on' => '2026-07-01', 'ends_on' => '2026-07-21', 'status' => Plan::STATUS_PAUSED]);
    expect(app(FinishExpiredPlans::class)->handle())->toBe(1)->and(app(FinishExpiredPlans::class)->handle())->toBe(0);
    expect($expired->fresh()->status)->toBe(Plan::STATUS_FINISHED)->and($future->fresh()->status)->toBe(Plan::STATUS_ACTIVE)->and($paused->fresh()->status)->toBe(Plan::STATUS_PAUSED);
});

it('muestra una fila por plan activo y excluye vencidos y pacientes archivados', function () {
    $patient = patient();
    plan(['patient_id' => $patient->id, 'name' => 'Activo uno', 'status' => Plan::STATUS_ACTIVE]);
    plan(['patient_id' => $patient->id, 'name' => 'Activo dos', 'status' => Plan::STATUS_ACTIVE]);
    plan(['patient_id' => $patient->id, 'name' => 'Vencido', 'starts_on' => '2026-07-01', 'ends_on' => '2026-07-21', 'status' => Plan::STATUS_ACTIVE]);
    $archivedPatient = patient();
    plan(['patient_id' => $archivedPatient->id, 'name' => 'Oculto', 'status' => Plan::STATUS_ACTIVE]);
    $archivedPatient->delete();
    $this->actingAs(specialist())->get(route('dashboard'))->assertSee('Activo uno')->assertSee('Activo dos')->assertDontSee('Vencido')->assertDontSee('Oculto')->assertSee('Sin configurar');
});

it('protege escrituras de planes con CSRF', function () {
    $this->app->instance(ValidateCsrfToken::class, new class($this->app, $this->app['encrypter']) extends ValidateCsrfToken
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    });
    $this->actingAs(specialist())->post(route('plans.store'), validPlanData(patient()))->assertStatus(419);
});
