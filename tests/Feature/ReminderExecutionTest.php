<?php

use App\Models\Patient;
use App\Models\Plan;
use App\Models\ReminderExecution;
use App\Modules\Reminders\Actions\AcquireReminderExecution;
use App\Modules\Reminders\Actions\EvaluateReminderExecution;
use App\Modules\Reminders\Actions\ProcessDueReminders;
use App\Modules\Reminders\Enums\ReminderOutcome;
use App\Modules\Reminders\Enums\ReminderReasonCode;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-03 09:30:15', 'America/Lima'));
    Http::preventStrayRequests();
    config()->set('services.whatsapp', [
        'access_token' => 'test-access-token',
        'phone_number_id' => '1234567890',
        'graph_version' => 'v23.0',
        'template_name' => 'sonqo_maki_daily_reminder',
        'template_language' => 'es_PE',
        'connect_timeout' => 1,
        'timeout' => 2,
    ]);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function executableReminder(array $patientAttributes = [], array $planAttributes = []): array
{
    static $tokenSequence = 0;
    $tokenSequence++;
    $patient = patient($patientAttributes);
    $plan = plan(array_merge([
        'patient_id' => $patient->id,
        'starts_on' => '2026-08-01',
        'ends_on' => '2026-08-07',
        'status' => Plan::STATUS_ACTIVE,
    ], $planAttributes));
    $plan->reminderConfiguration()->update(['is_active' => true]);
    $schedule = $plan->reminderConfiguration->schedules()->create(['weekday' => 1, 'send_at' => '09:30']);
    $routine = assignedRoutine($plan, [], [['name' => 'Movilidad activa']]);
    $token = str_pad('secure-token-'.$tokenSequence, 43, 's');
    $link = $plan->publicLinks()->create([
        'token_hash' => hash('sha256', $token),
        'token_ciphertext' => Crypt::encryptString($token),
        'token_prefix' => substr($token, 0, 12),
    ]);

    return compact('patient', 'plan', 'schedule', 'routine', 'link', 'token');
}

function runDueReminder(): ReminderExecution
{
    Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test-1']]], 200)]);
    app(ProcessDueReminders::class)->handle(CarbonImmutable::now());

    return ReminderExecution::sole();
}

function expectOmitted(ReminderReasonCode $reason): void
{
    $execution = runDueReminder();
    expect($execution->outcome)->toBe(ReminderOutcome::Omitted)
        ->and($execution->reason_code)->toBe($reason)
        ->and($execution->completed_at)->not->toBeNull()
        ->and($execution->duration_ms)->toBeGreaterThanOrEqual(0);
    Http::assertNothingSent();
}

it('procesa únicamente el día ISO y minuto local exactos sin recuperar horarios pasados', function () {
    executableReminder();
    $otherMinute = executableReminder([], ['name' => 'Otro minuto']);
    $otherMinute['schedule']->update(['send_at' => '09:29']);
    $otherDay = executableReminder([], ['name' => 'Otro día']);
    $otherDay['schedule']->update(['weekday' => 2]);
    $deleted = executableReminder([], ['name' => 'Eliminado']);
    $deleted['schedule']->delete();

    runDueReminder();

    expect(ReminderExecution::count())->toBe(1)
        ->and(ReminderExecution::first()->plan->name)->toBe('Plan de prueba');
});

it('ignora horarios de pacientes o planes archivados', function () {
    $archivedPatient = executableReminder([], ['name' => 'Paciente archivado']);
    $archivedPatient['patient']->delete();
    $archivedPlan = executableReminder([], ['name' => 'Plan archivado']);
    $archivedPlan['plan']->delete();
    Http::fake();

    $result = app(ProcessDueReminders::class)->handle(CarbonImmutable::now());

    expect($result)->toBe(['due' => 0, 'acquired' => 0, 'skipped' => 0])
        ->and(ReminderExecution::count())->toBe(0);
    Http::assertNothingSent();
});

it('persiste el instante UTC correcto, snapshots y payload de plantilla aprobado', function () {
    ['patient' => $patient, 'plan' => $plan, 'routine' => $routine, 'link' => $link, 'token' => $token] = executableReminder();

    $execution = runDueReminder();

    expect($execution->outcome)->toBe(ReminderOutcome::Accepted)
        ->and($execution->scheduled_at->utc()->format('Y-m-d H:i:s'))->toBe('2026-08-03 14:30:00')
        ->and($execution->patient_name_snapshot)->toBe($patient->full_name)
        ->and($execution->recipient_phone_snapshot)->toBe($patient->whatsapp_phone)
        ->and($execution->plan_name_snapshot)->toBe($plan->name)
        ->and($execution->routine_id)->toBe($routine->id)
        ->and($execution->public_link_id)->toBe($link->id)
        ->and($execution->whatsapp_message_id)->toBe('wamid.test-1')
        ->and($execution->provider_http_status)->toBe(200);

    Http::assertSent(function ($request) use ($patient, $token): bool {
        $data = $request->data();

        return $request->url() === 'https://graph.facebook.com/v23.0/1234567890/messages'
            && $request->hasHeader('Authorization', 'Bearer test-access-token')
            && $data['type'] === 'template'
            && $data['to'] === ltrim($patient->whatsapp_phone, '+')
            && $data['template']['components'][0]['parameters'][0]['text'] === $patient->full_name
            && str_contains($data['template']['components'][0]['parameters'][1]['text'], $token);
    });
});

it('la ejecución repetida y una fila processing nunca se retoman ni llaman dos veces al proveedor', function () {
    ['schedule' => $schedule] = executableReminder();
    Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.once']]], 200)]);
    $action = app(ProcessDueReminders::class);

    $action->handle(CarbonImmutable::now());
    $action->handle(CarbonImmutable::now());

    expect(ReminderExecution::count())->toBe(1);
    Http::assertSentCount(1);

    ReminderExecution::query()->delete();
    $execution = app(AcquireReminderExecution::class)->handle($schedule, CarbonImmutable::now());
    expect($execution->outcome)->toBe(ReminderOutcome::Processing);
    $action->handle(CarbonImmutable::now());
    expect($execution->fresh()->outcome)->toBe(ReminderOutcome::Processing);
    Http::assertSentCount(1);
});

it('la adquisición usa un insert atómico con conflicto y conserva una sola fila', function () {
    ['schedule' => $schedule] = executableReminder();
    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });
    $action = app(AcquireReminderExecution::class);

    expect($action->handle($schedule, CarbonImmutable::now()))->not->toBeNull()
        ->and($action->handle($schedule, CarbonImmutable::now()))->toBeNull()
        ->and(ReminderExecution::count())->toBe(1)
        ->and(collect($queries)->contains(fn ($sql) => str_contains($sql, 'on conflict') && str_contains($sql, 'do nothing')))->toBeTrue();
});

it('procesa independientemente dos planes del mismo paciente a la misma hora', function () {
    $first = executableReminder();
    executableReminder([], ['patient_id' => $first['patient']->id, 'name' => 'Segundo plan']);
    $sequence = 0;
    Http::fake(function () use (&$sequence) {
        $sequence++;

        return Http::response(['messages' => [['id' => 'wamid.'.$sequence]]], 200);
    });

    app(ProcessDueReminders::class)->handle(CarbonImmutable::now());

    expect(ReminderExecution::count())->toBe(2);
    Http::assertSentCount(2);
});

it('omite un paciente inactivo', function () {
    executableReminder(['status' => Patient::STATUS_INACTIVE]);
    expectOmitted(ReminderReasonCode::PatientInactive);
});

it('omite un teléfono inválido', function () {
    ['patient' => $patient] = executableReminder();
    DB::statement('ALTER TABLE patients DROP CONSTRAINT patients_whatsapp_phone_format_check');
    $patient->update(['whatsapp_phone' => '999']);
    expectOmitted(ReminderReasonCode::InvalidPhone);
});

it('omite la falta de consentimiento', function () {
    executableReminder(['whatsapp_consented_on' => null]);
    expectOmitted(ReminderReasonCode::NoConsent);
});

it('omite un plan pausado o finalizado', function (string $status) {
    executableReminder([], ['status' => $status]);
    expectOmitted(ReminderReasonCode::PlanNotActive);
})->with([Plan::STATUS_PAUSED, Plan::STATUS_FINISHED]);

it('omite una fecha fuera del rango aunque el estado persistido siga activo', function () {
    executableReminder([], ['starts_on' => '2026-07-01', 'ends_on' => '2026-08-02']);
    expectOmitted(ReminderReasonCode::OutsidePlanRange);
});

it('omite recordatorios pausados', function () {
    ['plan' => $plan] = executableReminder();
    $plan->reminderConfiguration()->update(['is_active' => false]);
    expectOmitted(ReminderReasonCode::RemindersPaused);
});

it('omite cobertura incompleta fuera de la fecha vigente', function () {
    ['plan' => $plan, 'routine' => $routine] = executableReminder();
    $routine->delete();
    assignedRoutine($plan, ['starts_on' => '2026-08-01', 'ends_on' => '2026-08-03'], [['name' => 'Actual']]);
    assignedRoutine($plan, ['starts_on' => '2026-08-05', 'ends_on' => '2026-08-07'], [['name' => 'Posterior']]);
    expectOmitted(ReminderReasonCode::IncompleteRoutineCoverage);
});

it('omite cuando no existe rutina vigente', function () {
    ['plan' => $plan, 'routine' => $routine] = executableReminder();
    $routine->delete();
    assignedRoutine($plan, ['starts_on' => '2026-08-01', 'ends_on' => '2026-08-02'], [['name' => 'Anterior']]);
    assignedRoutine($plan, ['starts_on' => '2026-08-04', 'ends_on' => '2026-08-07'], [['name' => 'Posterior']]);
    expectOmitted(ReminderReasonCode::NoCurrentRoutine);
});

it('omite cuando existen varias rutinas vigentes', function () {
    ['plan' => $plan] = executableReminder();
    DB::statement('ALTER TABLE routines DROP CONSTRAINT routines_no_overlapping_dates');
    assignedRoutine($plan, ['starts_on' => '2026-08-03', 'ends_on' => '2026-08-04'], [['name' => 'Solapada']]);
    expectOmitted(ReminderReasonCode::MultipleCurrentRoutines);
});

it('omite superposiciones fuera de la rutina vigente', function () {
    ['plan' => $plan, 'routine' => $routine] = executableReminder();
    $routine->delete();
    DB::statement('ALTER TABLE routines DROP CONSTRAINT routines_no_overlapping_dates');
    assignedRoutine($plan, ['starts_on' => '2026-08-01', 'ends_on' => '2026-08-04'], [['name' => 'Actual']]);
    assignedRoutine($plan, ['starts_on' => '2026-08-04', 'ends_on' => '2026-08-07'], [['name' => 'Solapada futura']]);
    expectOmitted(ReminderReasonCode::OverlappingRoutines);
});

it('omite una rutina vigente sin ejercicios y conserva su referencia', function () {
    ['routine' => $routine] = executableReminder();
    $routine->exercises()->delete();
    expectOmitted(ReminderReasonCode::RoutineWithoutExercises);
    expect(ReminderExecution::first()->routine_id)->toBe($routine->id);
});

it('omite enlaces ausentes, revocados o corruptos sin persistir el token', function (string $condition) {
    ['plan' => $plan, 'link' => $link, 'token' => $token] = executableReminder();
    if ($condition === 'missing') {
        $link->delete();
    } elseif ($condition === 'revoked') {
        $link->update(['revoked_at' => now()]);
    } else {
        $link->update(['token_ciphertext' => Crypt::encryptString('otro-token')]);
    }

    expectOmitted(ReminderReasonCode::PublicLinkUnavailable);
    expect(json_encode(ReminderExecution::first()->getAttributes()))->not->toContain($token);
})->with(['missing', 'revoked', 'corrupt']);

it('registra respuestas HTTP, errores Meta y respuestas malformadas sin reintentar', function (array $body, int $status, string $code) {
    executableReminder();
    Http::fake(['graph.facebook.com/*' => Http::response($body, $status)]);

    $execution = app(ProcessDueReminders::class)->handle(CarbonImmutable::now());
    $stored = ReminderExecution::sole();

    expect($stored->outcome)->toBe(ReminderOutcome::Failed)
        ->and($stored->reason_code)->toBe(ReminderReasonCode::WhatsappError)
        ->and($stored->provider_error_code)->toBe($code)
        ->and($stored->provider_http_status)->toBe($status);
    Http::assertSentCount(1);
})->with([
    'HTTP 4xx' => [[], 400, 'HTTP_400'],
    'HTTP 5xx' => [[], 503, 'HTTP_503'],
    'Meta estructurado' => [['error' => ['code' => 131000, 'message' => 'Solicitud rechazada']], 400, '131000'],
    '2xx malformada' => [['messages' => []], 200, 'MALFORMED_RESPONSE'],
]);

it('sanitiza URLs y credenciales declaradas en errores del proveedor', function () {
    ['token' => $token] = executableReminder();
    Http::fake(['graph.facebook.com/*' => Http::response([
        'error' => [
            'code' => 'invalid/token',
            'message' => "Falló https://example.test/mi-rutina/{$token} access_token=credencial-real",
        ],
    ], 400)]);

    app(ProcessDueReminders::class)->handle(CarbonImmutable::now());
    $execution = ReminderExecution::sole();

    expect($execution->provider_error_code)->toBe('INVALID_TOKEN')
        ->and($execution->provider_error_detail)->not->toContain($token)
        ->and($execution->provider_error_detail)->not->toContain('credencial-real')
        ->and($execution->provider_error_detail)->toContain('[URL_REDACTED]');
});

it('registra timeout o conexión fallida y no reintenta', function () {
    executableReminder();
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        throw new ConnectionException('secreto que no debe persistirse');
    });

    app(ProcessDueReminders::class)->handle(CarbonImmutable::now());
    $execution = ReminderExecution::sole();

    expect($execution->outcome)->toBe(ReminderOutcome::Failed)
        ->and($execution->provider_error_code)->toBe('CONNECTION_ERROR')
        ->and($execution->provider_error_detail)->not->toContain('secreto');
    expect($attempts)->toBe(1);
});

it('falla de forma segura si la configuración externa está incompleta sin abrir red', function () {
    executableReminder();
    config()->set('services.whatsapp.access_token', null);

    app(ProcessDueReminders::class)->handle(CarbonImmutable::now());
    $execution = ReminderExecution::sole();

    expect($execution->outcome)->toBe(ReminderOutcome::Failed)
        ->and($execution->provider_error_code)->toBe('CONFIGURATION_INCOMPLETE');
    Http::assertNothingSent();
});

it('convierte un fallo interno inesperado en failed con detalle genérico', function () {
    executableReminder();
    $mock = Mockery::mock(EvaluateReminderExecution::class);
    $mock->shouldReceive('handle')->once()->andThrow(new RuntimeException('token-super-secreto'));
    $this->app->instance(EvaluateReminderExecution::class, $mock);
    Log::spy();

    $execution = runDueReminder();

    expect($execution->outcome)->toBe(ReminderOutcome::Failed)
        ->and($execution->reason_code)->toBe(ReminderReasonCode::UnexpectedError)
        ->and($execution->provider_error_detail)->toBe('Ocurrió un error interno inesperado.')
        ->and(json_encode($execution->getAttributes()))->not->toContain('token-super-secreto');
    Http::assertNothingSent();
});

it('PostgreSQL restringe los outcomes', function () {
    ['schedule' => $schedule] = executableReminder();
    $execution = app(AcquireReminderExecution::class)->handle($schedule, CarbonImmutable::now());

    expect(fn () => DB::table('reminder_executions')->where('id', $execution->id)->update(['outcome' => 'delivered']))->toThrow(QueryException::class);
});

it('PostgreSQL restringe la duración a valores no negativos', function () {
    ['schedule' => $schedule] = executableReminder();
    $execution = app(AcquireReminderExecution::class)->handle($schedule, CarbonImmutable::now());

    expect(fn () => DB::table('reminder_executions')->where('id', $execution->id)->update(['duration_ms' => -1]))->toThrow(QueryException::class);
});

it('PostgreSQL exige correlation_id único', function () {
    $first = executableReminder();
    $second = executableReminder();
    $one = app(AcquireReminderExecution::class)->handle($first['schedule'], CarbonImmutable::now());
    $two = app(AcquireReminderExecution::class)->handle($second['schedule'], CarbonImmutable::now());

    expect(fn () => DB::table('reminder_executions')->where('id', $two->id)->update(['correlation_id' => $one->correlation_id]))->toThrow(QueryException::class);
});

it('PostgreSQL exige whatsapp_message_id único solo cuando existe', function () {
    $first = executableReminder();
    $second = executableReminder();
    $one = app(AcquireReminderExecution::class)->handle($first['schedule'], CarbonImmutable::now());
    $two = app(AcquireReminderExecution::class)->handle($second['schedule'], CarbonImmutable::now());
    $one->update(['whatsapp_message_id' => 'wamid.duplicado']);

    expect(fn () => $two->update(['whatsapp_message_id' => 'wamid.duplicado']))->toThrow(QueryException::class);
});

it('conserva el historial cuando se archivan paciente y plan', function () {
    ['patient' => $patient, 'plan' => $plan] = executableReminder();
    $execution = runDueReminder();
    $plan->delete();
    $patient->delete();

    expect($execution->fresh())->not->toBeNull()
        ->and($execution->fresh()->plan->trashed())->toBeTrue()
        ->and($execution->fresh()->patient->trashed())->toBeTrue();
});

it('registra el comando cada minuto con zona Lima y prevención de solapamiento', function () {
    Artisan::call('schedule:list');
    $events = app(Schedule::class)->events();
    $event = collect($events)->first(fn ($event) => str_contains($event->command ?? '', 'reminders:process-due'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('* * * * *')
        ->and($event->timezone)->toBe('America/Lima')
        ->and($event->withoutOverlapping)->toBeTrue();
});
