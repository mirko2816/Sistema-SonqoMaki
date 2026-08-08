<?php

use App\Models\ReminderExecution;
use App\Modules\Reminders\Enums\ReminderOutcome;
use App\Modules\Reminders\Enums\ReminderReasonCode;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function historicalExecution(array $attributes = []): ReminderExecution
{
    static $sequence = 0;
    $sequence++;
    $plan = $attributes['plan'] ?? plan();
    $patient = $attributes['patient'] ?? $plan->patient;
    unset($attributes['plan'], $attributes['patient']);
    $local = CarbonImmutable::parse($attributes['scheduled_local_date'] ?? '2026-08-03', 'America/Lima')
        ->setTime(8, $sequence % 60);

    return ReminderExecution::create(array_merge([
        'correlation_id' => (string) Str::uuid(),
        'plan_id' => $plan->id,
        'patient_id' => $patient->id,
        'scheduled_local_date' => $local->toDateString(),
        'scheduled_local_time' => $local->format('H:i:s'),
        'scheduled_at' => $local->utc(),
        'started_at' => $local->utc()->addSeconds(2),
        'completed_at' => $local->utc()->addSeconds(3),
        'outcome' => ReminderOutcome::Accepted,
        'patient_name_snapshot' => $patient->full_name,
        'recipient_phone_snapshot' => $patient->whatsapp_phone,
        'plan_name_snapshot' => $plan->name,
        'duration_ms' => 24,
    ], $attributes));
}

it('protege el listado y el detalle del historial para visitantes', function () {
    $execution = historicalExecution();

    $this->get(route('reminder-executions.index'))->assertRedirect(route('login'));
    $this->get(route('reminder-executions.show', $execution))->assertRedirect(route('login'));
});

it('ordena de la ejecución más reciente a la más antigua', function () {
    $older = historicalExecution(['patient_name_snapshot' => 'Paciente anterior', 'scheduled_local_date' => '2026-08-01']);
    $newer = historicalExecution(['patient_name_snapshot' => 'Paciente reciente', 'scheduled_local_date' => '2026-08-05']);

    $this->actingAs(specialist())->get(route('reminder-executions.index'))
        ->assertOk()
        ->assertSeeInOrder([$newer->patient_name_snapshot, $older->patient_name_snapshot]);
});

it('presenta los cuatro resultados técnicos sin llamar exitoso a accepted', function () {
    foreach (ReminderOutcome::cases() as $outcome) {
        historicalExecution(['outcome' => $outcome]);
    }

    $this->actingAs(specialist())->get(route('reminder-executions.index'))
        ->assertOk()
        ->assertSee('Procesando')
        ->assertSee('Omitido')
        ->assertSee('Aceptado por WhatsApp')
        ->assertSee('Fallido')
        ->assertDontSee('Exitoso');
});

it('muestra snapshots aunque paciente y plan cambien y sean archivados', function () {
    $patient = patient(['first_names' => 'Nombre', 'last_names' => 'Histórico']);
    $plan = plan(['patient_id' => $patient->id, 'name' => 'Plan histórico']);
    $execution = historicalExecution(['patient' => $patient, 'plan' => $plan]);
    $plan->update(['name' => 'Plan renombrado']);
    $patient->update(['first_names' => 'Nombre cambiado']);
    $plan->delete();
    $patient->delete();

    $this->actingAs(specialist())->get(route('reminder-executions.index'))
        ->assertOk()
        ->assertSee($execution->patient_name_snapshot)
        ->assertSee($execution->plan_name_snapshot)
        ->assertDontSee('Nombre cambiado')
        ->assertDontSee('Plan renombrado');
});

it('filtra por resultado paciente plan y fechas y conserva parámetros al paginar', function () {
    $patient = patient(['first_names' => 'Paciente', 'last_names' => 'Filtrado']);
    $plan = plan(['patient_id' => $patient->id, 'name' => 'Plan filtrado']);
    foreach (range(1, 16) as $day) {
        historicalExecution([
            'patient' => $patient,
            'plan' => $plan,
            'outcome' => ReminderOutcome::Accepted,
            'scheduled_local_date' => sprintf('2026-07-%02d', $day),
        ]);
    }
    historicalExecution(['outcome' => ReminderOutcome::Failed, 'patient_name_snapshot' => 'No debe aparecer']);
    $filters = [
        'outcome' => 'accepted',
        'patient' => $patient->id,
        'plan' => $plan->id,
        'date_from' => '2026-07-01',
        'date_to' => '2026-07-31',
    ];

    $response = $this->actingAs(specialist())->get(route('reminder-executions.index', $filters))
        ->assertOk()
        ->assertSee('Paciente Filtrado');
    $executions = $response->viewData('executions');
    $pageTwo = $executions->url(2);

    expect($executions->total())->toBe(16)
        ->and($executions->every(fn ($execution) => $execution->outcome === ReminderOutcome::Accepted
            && $execution->patient_id === $patient->id
            && $execution->plan_id === $plan->id))->toBeTrue()
        ->and($pageTwo)->toContain('outcome=accepted')
        ->toContain('patient='.$patient->id)
        ->toContain('plan='.$plan->id)
        ->toContain('date_from=2026-07-01')
        ->toContain('date_to=2026-07-31');

    expect($this->get(route('reminder-executions.index', ['date_to' => '2026-07-08']))
        ->assertOk()->viewData('executions')->total())->toBe(8);
});

it('distingue el estado sin ejecuciones del estado sin resultados', function () {
    $user = specialist();
    $this->actingAs($user)->get(route('reminder-executions.index'))
        ->assertOk()->assertSee('Todavía no hay ejecuciones');

    historicalExecution(['outcome' => ReminderOutcome::Accepted]);
    $this->get(route('reminder-executions.index', ['outcome' => 'failed']))
        ->assertOk()->assertSee('No hay resultados para estos filtros');
});

it('muestra el detalle técnico de ejecuciones aceptadas omitidas y fallidas', function (ReminderOutcome $outcome, array $attributes, array $expected) {
    $execution = historicalExecution(array_merge(['outcome' => $outcome], $attributes));
    $response = $this->actingAs(specialist())->get(route('reminder-executions.show', $execution))->assertOk();

    foreach ($expected as $text) {
        $response->assertSee($text);
    }
})->with([
    'aceptada' => [ReminderOutcome::Accepted, [
        'whatsapp_message_id' => 'wamid.history-accepted',
        'provider_http_status' => 200,
    ], ['Aceptado por WhatsApp', 'wamid.history-accepted', '200', '24 ms']],
    'omitida' => [ReminderOutcome::Omitted, [
        'reason_code' => ReminderReasonCode::NoConsent,
        'whatsapp_message_id' => null,
        'provider_http_status' => null,
    ], ['Omitido', 'Sin consentimiento para WhatsApp']],
    'fallida' => [ReminderOutcome::Failed, [
        'reason_code' => ReminderReasonCode::WhatsappError,
        'provider_http_status' => 503,
        'provider_error_code' => 'HTTP_503',
        'provider_error_detail' => 'Servicio temporalmente no disponible.',
    ], ['Fallido', '503', 'HTTP_503', 'Servicio temporalmente no disponible.']],
]);

it('no expone tokens credenciales teléfonos ni detalles sensibles', function () {
    $publicToken = 'token-publico-super-secreto-123456789';
    $credential = 'credencial-whatsapp-real';
    config()->set('services.whatsapp.access_token', $credential);
    $execution = historicalExecution([
        'provider_error_detail' => "Falló https://example.test/mi-rutina/{$publicToken} access_token={$credential}",
    ]);

    $this->actingAs(specialist())->get(route('reminder-executions.show', $execution))
        ->assertOk()
        ->assertDontSee($publicToken)
        ->assertDontSee($credential)
        ->assertDontSee($execution->recipient_phone_snapshot)
        ->assertSee('[URL_REDACTED]')
        ->assertSee('access_token=[REDACTED]');
});

it('carga horarios y rutinas del listado sin consultas N más uno', function () {
    foreach (range(1, 10) as $index) {
        $plan = plan(['name' => 'Plan '.$index]);
        $schedule = $plan->reminderConfiguration->schedules()->create(['weekday' => 1, 'send_at' => '08:00']);
        $routine = assignedRoutine($plan, ['name' => 'Rutina '.$index]);
        historicalExecution(['plan' => $plan, 'patient' => $plan->patient, 'reminder_schedule_id' => $schedule->id, 'routine_id' => $routine->id]);
    }
    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });

    $this->actingAs(specialist())->get(route('reminder-executions.index'))
        ->assertOk()->assertSee('Horario 08:00')->assertSee('Rutina 1');

    expect(collect($queries)->filter(fn ($query) => str_contains($query, 'reminder_schedules'))->count())->toBe(1)
        ->and(collect($queries)->filter(fn ($query) => str_contains($query, 'routines'))->count())->toBe(1)
        ->and(collect($queries)->filter(fn ($query) => str_contains($query, 'patients'))->count())->toBe(0)
        ->and(collect($queries)->filter(fn ($query) => str_contains($query, 'plans'))->count())->toBe(0);
});

it('registra únicamente rutas de lectura para el historial', function () {
    $routes = collect(app('router')->getRoutes()->getRoutes())
        ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'reminder-executions.'));

    expect($routes)->toHaveCount(2);
    $routes->each(fn ($route) => expect($route->methods())->toBe(['GET', 'HEAD']));

    $this->actingAs(specialist())->post('/historial-recordatorios')->assertMethodNotAllowed();
});
