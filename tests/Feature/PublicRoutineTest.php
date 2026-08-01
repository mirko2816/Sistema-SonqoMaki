<?php

use App\Models\Plan;
use App\Modules\Plans\Actions\ArchivePlan;
use App\Modules\PublicPortal\Actions\ComposePublicRoutineUrl;
use App\Modules\PublicPortal\Actions\DecidePublicRoutine;
use App\Modules\PublicPortal\Actions\ResolvePublicLink;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-22 10:00', 'America/Lima'));
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function publicPlan(array $planAttributes = [], array $exerciseAttributes = []): array
{
    $patient = patient([
        'first_names' => 'NombrePrivado',
        'last_names' => 'ApellidoPrivado',
    ]);
    $plan = plan(array_merge([
        'patient_id' => $patient->id,
        'name' => 'PlanInternoPrivado',
        'starts_on' => '2026-07-20',
        'ends_on' => '2026-07-25',
        'status' => Plan::STATUS_ACTIVE,
    ], $planAttributes));
    $routine = assignedRoutine($plan, [
        'name' => 'Movilidad de hoy',
        'starts_on' => '2026-07-20',
        'ends_on' => '2026-07-25',
    ], [array_merge([
        'name' => 'Elevación de brazo',
        'description' => 'Sube el brazo lentamente.',
        'sets' => 3,
        'repetitions' => 8,
        'duration_seconds' => 90,
        'material_url' => 'https://material.example/guia?paso=1',
    ], $exerciseAttributes)]);

    $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $link = $plan->publicLinks()->create([
        'token_hash' => hash('sha256', $token),
        'token_ciphertext' => Crypt::encryptString($token),
        'token_prefix' => substr($token, 0, 10),
    ]);

    return compact('patient', 'plan', 'routine', 'token', 'link');
}

it('expone una ruta pública nombrada sin autenticación ni identificadores secuenciales', function () {
    ['plan' => $plan, 'token' => $token] = publicPlan();

    $url = route('public-routine.show', $token);

    expect($url)->toContain('/mi-rutina/'.$token)->not->toContain('/'.$plan->id.'/');
    $this->get($url)
        ->assertOk()
        ->assertHeaderMissing('Set-Cookie')
        ->assertSee('Tu rutina de hoy')
        ->assertDontSee('Iniciar sesión');
});

it('resuelve exclusivamente por el hash del token y muestra el plan correcto', function () {
    publicPlan(['name' => 'Plan que no corresponde']);
    ['token' => $token] = publicPlan([], ['name' => 'Ejercicio correcto']);

    $this->get(route('public-routine.show', $token))
        ->assertOk()
        ->assertSee('Ejercicio correcto')
        ->assertDontSee('Plan que no corresponde');
});

it('presenta exactamente el mismo estado genérico para token inválido y revocado', function () {
    ['token' => $token, 'link' => $link] = publicPlan();
    $link->update(['revoked_at' => now()]);

    $invalid = $this->get(route('public-routine.show', str_repeat('Z', 43)));
    $revoked = $this->get(route('public-routine.show', $token));

    $invalid->assertOk()->assertSee('No podemos mostrar una rutina en este momento');
    $revoked->assertOk()->assertSee('No podemos mostrar una rutina en este momento');
    expect($invalid->getContent())->toBe($revoked->getContent());
});

it('no persiste ni registra el token completo en texto plano', function () {
    Log::spy();
    ['token' => $token, 'link' => $link] = publicPlan();

    expect($link->token_hash)->not->toBe($token)
        ->and($link->token_ciphertext)->not->toContain($token)
        ->and(DB::table('public_links')->where('id', $link->id)->value('token_ciphertext'))->not->toContain($token);

    $this->get(route('public-routine.show', $token))->assertOk();
    Log::shouldNotHaveReceived('debug');
    Log::shouldNotHaveReceived('info');
    Log::shouldNotHaveReceived('warning');
    Log::shouldNotHaveReceived('error');
});

it('mantiene un único enlace vigente por plan mediante PostgreSQL', function () {
    ['plan' => $plan] = publicPlan();

    expect(fn () => $plan->publicLinks()->create([
        'token_hash' => hash('sha256', str_repeat('B', 43)),
        'token_ciphertext' => Crypt::encryptString(str_repeat('B', 43)),
        'token_prefix' => str_repeat('B', 10),
    ]))->toThrow(QueryException::class);
});

it('recompone la URL futura desde APP_URL sin rotar ni duplicar el enlace', function () {
    config()->set('app.url', 'https://rutinas.sonqomaki.test/base/');
    ['plan' => $plan, 'token' => $token] = publicPlan();

    $url = app(ComposePublicRoutineUrl::class)->handle($plan);

    expect($url)->toBe('https://rutinas.sonqomaki.test/base/mi-rutina/'.$token)
        ->and($plan->publicLinks()->count())->toBe(1);
});

it('rechaza la composición de enlaces revocados, archivados o alterados', function () {
    ['plan' => $plan, 'link' => $link] = publicPlan();
    $link->update(['revoked_at' => now()]);
    expect(fn () => app(ComposePublicRoutineUrl::class)->handle($plan))->toThrow(LogicException::class);

    ['plan' => $alteredPlan, 'link' => $altered] = publicPlan();
    $altered->update(['token_ciphertext' => Crypt::encryptString(str_repeat('C', 43))]);
    expect(fn () => app(ComposePublicRoutineUrl::class)->handle($alteredPlan))->toThrow(RuntimeException::class);

    ['plan' => $archivedPlan] = publicPlan();
    $archivedPlan->delete();
    expect(fn () => app(ComposePublicRoutineUrl::class)->handle($archivedPlan))->toThrow(LogicException::class);
});

it('oculta toda información del paciente, especialista, plan e identificadores internos', function () {
    ['patient' => $patient, 'plan' => $plan, 'routine' => $routine, 'token' => $token] = publicPlan();
    $specialist = specialist();

    $this->get(route('public-routine.show', $token))
        ->assertOk()
        ->assertDontSee($patient->first_names)
        ->assertDontSee($patient->last_names)
        ->assertDontSee($patient->dni)
        ->assertDontSee($patient->whatsapp_phone)
        ->assertDontSee($specialist->email)
        ->assertDontSee($plan->name)
        ->assertDontSee('plan_id', false)
        ->assertDontSee('patient_id', false)
        ->assertDontSee('/planes/'.$plan->id, false)
        ->assertDontSee('/pacientes/'.$patient->id, false)
        ->assertDontSee('/rutinas/'.$routine->id, false);
});

it('trata pacientes y planes archivados como contenido no disponible aunque no se revoque manualmente', function () {
    ['patient' => $patient, 'token' => $patientToken] = publicPlan([], ['name' => 'DatoPacienteArchivado']);
    $patient->delete();
    $this->get(route('public-routine.show', $patientToken))
        ->assertSee('No podemos mostrar una rutina')
        ->assertDontSee('DatoPacienteArchivado');

    ['plan' => $plan, 'token' => $planToken] = publicPlan([], ['name' => 'DatoPlanArchivado']);
    $plan->delete();
    $this->get(route('public-routine.show', $planToken))
        ->assertSee('No podemos mostrar una rutina')
        ->assertDontSee('DatoPlanArchivado');
});

it('el archivo transaccional del plan revoca el acceso inmediatamente', function () {
    ['plan' => $plan, 'token' => $token, 'link' => $link] = publicPlan();

    app(ArchivePlan::class)->handle($plan);

    expect($link->fresh()->revoked_at)->not->toBeNull();
    $this->get(route('public-routine.show', $token))->assertSee('No podemos mostrar una rutina');
});

it('un plan en pausa muestra solo su estado estático', function () {
    ['plan' => $plan, 'token' => $token] = publicPlan([], ['name' => 'RutinaSecretaPausada']);
    $plan->update(['status' => Plan::STATUS_PAUSED]);

    $this->get(route('public-routine.show', $token))
        ->assertSee('Su plan de ejercicios se encuentra pausado')
        ->assertDontSee('RutinaSecretaPausada');
});

it('un plan finalizado muestra solo su estado estático', function () {
    ['plan' => $plan, 'token' => $token] = publicPlan([], ['name' => 'RutinaSecretaFinalizada']);
    $plan->update(['status' => Plan::STATUS_FINISHED]);

    $this->get(route('public-routine.show', $token))
        ->assertSee('Plan de ejercicios finalizado')
        ->assertDontSee('RutinaSecretaFinalizada');
});

it('no expone rutinas antes del inicio ni después del vencimiento persistido como activo', function () {
    ['plan' => $future, 'token' => $futureToken] = publicPlan([
        'starts_on' => '2026-07-23',
        'ends_on' => '2026-07-30',
    ], ['name' => 'RutinaFuturaOculta']);
    $future->routines()->update(['starts_on' => '2026-07-23', 'ends_on' => '2026-07-30']);
    $this->get(route('public-routine.show', $futureToken))
        ->assertSee('No podemos mostrar una rutina')
        ->assertDontSee('RutinaFuturaOculta');

    ['plan' => $expired, 'token' => $expiredToken] = publicPlan([
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-07-21',
    ], ['name' => 'RutinaPasadaOculta']);
    $expired->routines()->update(['starts_on' => '2026-07-01', 'ends_on' => '2026-07-21']);
    $this->get(route('public-routine.show', $expiredToken))
        ->assertSee('Plan de ejercicios finalizado')
        ->assertDontSee('RutinaPasadaOculta');
});

it('un plan activo sin una única rutina utilizable muestra el estado genérico', function () {
    ['routine' => $routine, 'token' => $token] = publicPlan();
    $routine->delete();

    $this->get(route('public-routine.show', $token))
        ->assertSee('No podemos mostrar una rutina')
        ->assertDontSee('Movilidad de hoy');

    ['routine' => $emptyRoutine, 'token' => $emptyToken] = publicPlan();
    $emptyRoutine->exercises()->delete();
    $this->get(route('public-routine.show', $emptyToken))->assertSee('No podemos mostrar una rutina');
});

it('no selecciona arbitrariamente cuando hay más de una rutina vigente', function () {
    ['plan' => $plan] = publicPlan();

    DB::statement('ALTER TABLE routines DROP CONSTRAINT routines_no_overlapping_dates');
    $second = assignedRoutine($plan, [
        'name' => 'Segunda inconsistente',
        'starts_on' => '2026-07-21',
        'ends_on' => '2026-07-23',
    ], [['name' => 'No debe elegirse']]);

    $result = app(DecidePublicRoutine::class)->handle($plan->fresh());

    expect($result->state)->toBe('unavailable')->and($result->routine)->toBeNull();
    $second->delete();
});

it('un enlace antiguo refleja inmediatamente los cambios de estado y rutina', function () {
    ['plan' => $plan, 'routine' => $routine, 'token' => $token] = publicPlan();
    $this->get(route('public-routine.show', $token))->assertSee('Movilidad de hoy');

    $routine->update(['name' => 'Rutina actualizada']);
    $this->get(route('public-routine.show', $token))->assertSee('Rutina actualizada')->assertDontSee('Movilidad de hoy');

    $plan->update(['status' => Plan::STATUS_PAUSED]);
    $this->get(route('public-routine.show', $token))->assertSee('plan de ejercicios se encuentra pausado')->assertDontSee('Rutina actualizada');
});

it('muestra solo la rutina vigente y ordena sus ejercicios copiados', function () {
    ['plan' => $plan, 'routine' => $current, 'token' => $token] = publicPlan([], ['name' => 'Segundo']);
    $current->exercises()->where('name', 'Segundo')->update(['position' => 2]);
    $current->exercises()->create(['position' => 1, 'name' => 'Primero']);
    assignedRoutine($plan, [
        'name' => 'Rutina futura privada',
        'starts_on' => '2026-07-26',
        'ends_on' => '2026-07-28',
    ], [['name' => 'Ejercicio futuro']]);

    $response = $this->get(route('public-routine.show', $token))
        ->assertSee('Movilidad de hoy')
        ->assertDontSee('Rutina futura privada')
        ->assertDontSee('Ejercicio futuro');

    $response->assertSeeInOrder(['Primero', 'Segundo']);
});

it('usa las copias aunque cambie o se archive el ejercicio original', function () {
    $source = exercise(['name' => 'Biblioteca original']);
    ['routine' => $routine, 'token' => $token] = publicPlan([], [
        'source_exercise_id' => $source->id,
        'name' => 'Copia estable',
    ]);
    $source->update(['name' => 'Biblioteca modificada']);
    $source->delete();

    $this->get(route('public-routine.show', $token))
        ->assertSee('Copia estable')
        ->assertDontSee('Biblioteca original')
        ->assertDontSee('Biblioteca modificada');
    expect($routine->exercises->first()->sourceExercise()->withTrashed()->exists())->toBeTrue();
});

it('presenta campos opcionales sin ruido y unidades comprensibles', function () {
    ['token' => $token] = publicPlan([], [
        'description' => null,
        'sets' => null,
        'repetitions' => null,
        'duration_seconds' => null,
        'material_url' => null,
    ]);

    $this->get(route('public-routine.show', $token))
        ->assertSee('Este ejercicio no tiene material externo')
        ->assertDontSee('Series')
        ->assertDontSee('Repeticiones')
        ->assertDontSee('Duración');

    ['token' => $completeToken] = publicPlan();
    $this->get(route('public-routine.show', $completeToken))
        ->assertSee('Series')
        ->assertSee('Repeticiones')
        ->assertSee('1 min 30 s');
});

it('renderiza materiales externos con atributos seguros y descarta esquemas manipulados', function () {
    ['token' => $token] = publicPlan();
    $this->get(route('public-routine.show', $token))
        ->assertSee('href="https://material.example/guia?paso=1"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSee('referrerpolicy="no-referrer"', false);

    ['routine' => $routine, 'token' => $unsafeToken] = publicPlan();
    DB::table('routine_exercises')->where('routine_id', $routine->id)->update(['material_url' => 'javascript:alert(1)']);
    $this->get(route('public-routine.show', $unsafeToken))
        ->assertDontSee('javascript:', false)
        ->assertSee('Este ejercicio no tiene material externo');
});

it('usa America Lima alrededor del cambio de día UTC', function () {
    ['token' => $token] = publicPlan([
        'starts_on' => '2026-07-22',
        'ends_on' => '2026-07-22',
    ]);
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-23 04:30:00', 'UTC'));

    $this->get(route('public-routine.show', $token))->assertSee('Movilidad de hoy');

    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-23 05:30:00', 'UTC'));
    $this->get(route('public-routine.show', $token))->assertSee('Plan de ejercicios finalizado');
});

it('incluye los límites inicial y final de la rutina', function (string $today) {
    ['token' => $token] = publicPlan([
        'starts_on' => '2026-07-20',
        'ends_on' => '2026-07-25',
    ]);
    CarbonImmutable::setTestNow(CarbonImmutable::parse($today.' 12:00', 'America/Lima'));

    $this->get(route('public-routine.show', $token))->assertSee('Movilidad de hoy');
})->with(['inicio' => '2026-07-20', 'fin' => '2026-07-25']);

it('aplica encabezados de seguridad y no expone el token en HTML o JavaScript', function () {
    ['token' => $token] = publicPlan();

    $response = $this->get(route('public-routine.show', $token))
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
        ->assertHeader('Referrer-Policy', 'no-referrer')
        ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-store, private')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Content-Security-Policy');

    expect($response->getContent())->not->toContain($token)
        ->and($response->getContent())->not->toContain('<script')
        ->and($response->getContent())->toMatch('/<link rel="stylesheet" href="[^"]*\/build\/assets\/[^"]+\.css">/');
});

it('convierte fallos internos en el estado genérico sin mostrar trazas ni el token', function () {
    $token = str_repeat('T', 43);
    $resolver = Mockery::mock(ResolvePublicLink::class);
    $resolver->shouldReceive('handle')->once()->with($token)->andThrow(new RuntimeException('detalle-interno-secreto'));
    $this->app->instance(ResolvePublicLink::class, $resolver);

    $response = $this->get(route('public-routine.show', $token))
        ->assertOk()
        ->assertSee('No podemos mostrar una rutina');

    expect($response->getContent())->not->toContain('detalle-interno-secreto')
        ->and($response->getContent())->not->toContain($token);
});
