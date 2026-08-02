<?php

use App\Models\Plan;
use App\Models\ReminderConfiguration;
use App\Models\ReminderSchedule;
use App\Modules\Patients\Actions\ArchivePatient;
use App\Modules\Plans\Actions\ArchivePlan;
use App\Modules\Plans\Actions\CreatePlan;
use App\Modules\Plans\Actions\DuplicatePlan;
use App\Modules\Reminders\Actions\SaveReminderConfiguration;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function reminderPayload(array $schedules = [], bool $active = false): array
{
    return ['is_active' => $active, 'schedules' => $schedules];
}

it('protege todas las rutas de recordatorios con autenticación', function () {
    $plan = plan();

    $this->get(route('reminders.index'))->assertRedirect(route('login'));
    $this->get(route('reminders.edit', $plan))->assertRedirect(route('login'));
    $this->put(route('reminders.update', $plan), reminderPayload())->assertRedirect(route('login'));
});

it('crea automáticamente una configuración inactiva para planes nuevos', function () {
    $patient = patient();
    $plan = app(CreatePlan::class)->handle([
        'patient_id' => $patient->id, 'name' => 'Plan nuevo',
        'starts_on' => '2026-08-01', 'ends_on' => '2026-08-07',
    ]);

    expect($plan->reminderConfiguration)->not->toBeNull()
        ->and($plan->reminderConfiguration->is_active)->toBeFalse()
        ->and(ReminderConfiguration::where('plan_id', $plan->id)->count())->toBe(1);
});

it('la migración hace backfill real de planes existentes y revierte con seguridad', function () {
    $executionMigration = require database_path('migrations/2026_08_01_000009_create_reminder_executions_table.php');
    $migration = require database_path('migrations/2026_08_01_000008_create_reminder_configuration_tables.php');
    $executionMigration->down();
    $migration->down();
    $legacyPlan = Plan::create([
        'patient_id' => patient()->id, 'name' => 'Plan anterior a recordatorios',
        'starts_on' => '2026-08-01', 'ends_on' => '2026-08-07', 'status' => Plan::STATUS_PAUSED,
    ]);

    $migration->up();
    $executionMigration->up();

    expect(ReminderConfiguration::where('plan_id', $legacyPlan->id)->value('is_active'))->toBeFalse();
});

it('acepta configuración vacía y uno o dos horarios por día', function () {
    $plan = plan();
    $action = app(SaveReminderConfiguration::class);

    $action->handle($plan, reminderPayload());
    expect($plan->reminderConfiguration->schedules()->count())->toBe(0);

    $saved = $action->handle($plan, reminderPayload([1 => ['08:00'], 2 => ['09:00', '17:30']], true));
    expect($saved->schedules)->toHaveCount(3)->and($saved->is_active)->toBeTrue();
});

it('rechaza un tercer horario, duplicados y días fuera del rango ISO', function () {
    $plan = plan();
    $action = app(SaveReminderConfiguration::class);

    expect(fn () => $action->handle($plan, reminderPayload([1 => ['08:00', '12:00', '18:00']])))
        ->toThrow(ValidationException::class);
    expect(fn () => $action->handle($plan, reminderPayload([1 => ['08:00', '08:00']])))
        ->toThrow(ValidationException::class);
    expect(fn () => $action->handle($plan, reminderPayload([0 => ['08:00']])))
        ->toThrow(ValidationException::class);
    expect(fn () => $action->handle($plan, reminderPayload([8 => ['08:00']])))
        ->toThrow(ValidationException::class);
});

it('bloquea la configuración para serializar solicitudes concurrentes antes de sincronizar', function () {
    $plan = plan();
    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });

    app(SaveReminderConfiguration::class)->handle($plan, reminderPayload([1 => ['08:00', '18:00']]));

    expect(collect($queries)->contains(fn ($query) => str_contains($query, 'reminder_configurations') && str_contains($query, 'for update')))->toBeTrue()
        ->and($plan->reminderConfiguration->schedules()->count())->toBe(2);
});

it('mantiene independencia entre días y planes del mismo paciente', function () {
    $patient = patient();
    $first = plan(['patient_id' => $patient->id, 'name' => 'Primero']);
    $second = plan(['patient_id' => $patient->id, 'name' => 'Segundo']);

    app(SaveReminderConfiguration::class)->handle($first, reminderPayload([1 => ['08:00'], 2 => ['08:00']]));
    app(SaveReminderConfiguration::class)->handle($second, reminderPayload([1 => ['08:00', '18:00']]));

    expect($first->reminderConfiguration->schedules()->count())->toBe(2)
        ->and($second->reminderConfiguration->schedules()->count())->toBe(2);
});

it('PostgreSQL impide días fuera del rango ISO', function () {
    $plan = plan();
    expect(fn () => $plan->reminderConfiguration->schedules()->create(['weekday' => 0, 'send_at' => '09:00']))->toThrow(QueryException::class);
});

it('PostgreSQL impide más de una configuración por plan', function () {
    $plan = plan();
    expect(fn () => ReminderConfiguration::create(['plan_id' => $plan->id, 'is_active' => false]))->toThrow(QueryException::class);
});

it('PostgreSQL impide horarios activos duplicados', function () {
    $configuration = plan()->reminderConfiguration;
    $configuration->schedules()->create(['weekday' => 1, 'send_at' => '08:00']);
    expect(fn () => $configuration->schedules()->create(['weekday' => 1, 'send_at' => '08:00']))->toThrow(QueryException::class);
});

it('activa y pausa sin modificar el plan ni eliminar horarios', function () {
    $plan = plan(['status' => Plan::STATUS_PAUSED]);
    $action = app(SaveReminderConfiguration::class);
    $action->handle($plan, reminderPayload([3 => ['07:15']], true));

    expect($plan->fresh()->status)->toBe(Plan::STATUS_PAUSED)
        ->and($plan->reminderConfiguration->fresh()->is_active)->toBeTrue();

    $action->handle($plan, reminderPayload([3 => ['07:15']], false));
    expect($plan->fresh()->status)->toBe(Plan::STATUS_PAUSED)
        ->and($plan->reminderConfiguration->fresh()->is_active)->toBeFalse()
        ->and($plan->reminderConfiguration->schedules()->count())->toBe(1);
});

it('guarda horarios en planes incompletos, pausados o finalizados', function (string $status) {
    $plan = plan(['status' => $status]);
    app(SaveReminderConfiguration::class)->handle($plan, reminderPayload([7 => ['10:00']]));
    expect($plan->reminderConfiguration->schedules()->count())->toBe(1);
})->with([Plan::STATUS_PAUSED, Plan::STATUS_FINISHED]);

it('desactiva recordatorios al archivar paciente o plan y conserva horarios', function () {
    $patient = patient();
    $patientPlan = plan(['patient_id' => $patient->id]);
    $archivedPlan = plan();
    foreach ([$patientPlan, $archivedPlan] as $item) {
        app(SaveReminderConfiguration::class)->handle($item, reminderPayload([1 => ['08:00']], true));
    }

    app(ArchivePatient::class)->handle($patient);
    app(ArchivePlan::class)->handle($archivedPlan);

    foreach ([$patientPlan, $archivedPlan] as $item) {
        expect($item->reminderConfiguration->fresh()->is_active)->toBeFalse()
            ->and($item->reminderConfiguration->schedules()->count())->toBe(1);
    }
});

it('duplica con configuración propia inactiva y sin copiar horarios', function () {
    $source = plan();
    app(SaveReminderConfiguration::class)->handle($source, reminderPayload([1 => ['08:00']], true));
    $copy = app(DuplicatePlan::class)->handle($source, ['patient_id' => $source->patient_id, 'name' => 'Copia']);

    expect($copy->reminderConfiguration)->not->toBeNull()
        ->and($copy->reminderConfiguration->id)->not->toBe($source->reminderConfiguration->id)
        ->and($copy->reminderConfiguration->is_active)->toBeFalse()
        ->and($copy->reminderConfiguration->schedules()->count())->toBe(0);
});

it('sincroniza mediante borrado lógico y restaura un horario retirado', function () {
    $plan = plan();
    $action = app(SaveReminderConfiguration::class);
    $action->handle($plan, reminderPayload([1 => ['08:00', '18:00']]));
    $removedId = $plan->reminderConfiguration->schedules()->where('send_at', '18:00')->value('id');

    $action->handle($plan, reminderPayload([1 => ['08:00']]));
    expect(ReminderSchedule::withTrashed()->find($removedId)->trashed())->toBeTrue();

    $action->handle($plan, reminderPayload([1 => ['08:00', '18:00']]));
    expect(ReminderSchedule::find($removedId))->not->toBeNull()
        ->and(ReminderSchedule::withTrashed()->where('reminder_configuration_id', $plan->reminderConfiguration->id)->count())->toBe(2);
});

it('muestra los estados reales en dashboard y listado sin N más uno', function () {
    $none = plan(['name' => 'Sin agenda', 'status' => Plan::STATUS_ACTIVE]);
    $paused = plan(['name' => 'Agenda pausada', 'status' => Plan::STATUS_ACTIVE]);
    $active = plan(['name' => 'Agenda activa', 'status' => Plan::STATUS_ACTIVE]);
    app(SaveReminderConfiguration::class)->handle($paused, reminderPayload([1 => ['08:00']]));
    app(SaveReminderConfiguration::class)->handle($active, reminderPayload([2 => ['09:00']], true));
    $user = specialist();

    $this->actingAs($user)->get(route('dashboard'))->assertOk()
        ->assertSee('Sin horarios')->assertSee('Configurados y pausados')->assertSee('Activos');

    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });
    $this->get(route('reminders.index'))->assertOk()
        ->assertSee('Sin horarios')->assertSee('Configurados y pausados')->assertSee('Activos');
    expect(collect($queries)->filter(fn ($query) => str_contains($query, 'reminder_configurations'))->count())->toBe(1)
        ->and(collect($queries)->filter(fn ($query) => str_contains($query, 'patients'))->count())->toBeLessThanOrEqual(1);
});

it('guarda desde el formulario e informa la zona America Lima', function () {
    $plan = plan();
    $user = specialist();
    $this->actingAs($user)->get(route('reminders.edit', $plan))->assertOk()->assertSee('America/Lima');
    $this->put(route('reminders.update', $plan), reminderPayload([1 => ['08:00', '18:00']], true))
        ->assertRedirect(route('reminders.edit', $plan))->assertSessionHas('status');
    expect($plan->reminderConfiguration->fresh()->is_active)->toBeTrue();
});

it('protege la escritura con CSRF', function () {
    $this->app->instance(ValidateCsrfToken::class, new class($this->app, $this->app['encrypter']) extends ValidateCsrfToken
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    });

    $this->actingAs(specialist())->put(route('reminders.update', plan()), reminderPayload())->assertStatus(419);
});
