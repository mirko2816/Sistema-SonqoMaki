<?php

namespace App\Modules\Reminders\Actions;

use App\Models\Patient;
use App\Models\Plan;
use App\Models\ReminderExecution;
use App\Modules\PublicPortal\Actions\ComposePublicRoutineUrl;
use App\Modules\Reminders\Enums\ReminderReasonCode;
use App\Modules\Reminders\Support\ReminderEvaluation;
use Carbon\CarbonImmutable;
use Throwable;

class EvaluateReminderExecution
{
    public function __construct(private readonly ComposePublicRoutineUrl $composePublicRoutineUrl) {}

    public function handle(ReminderExecution $execution): ReminderEvaluation
    {
        $execution->loadMissing(['plan.patient', 'schedule.configuration']);
        $patient = $execution->patient;
        $plan = $execution->plan;
        $date = CarbonImmutable::parse($execution->scheduled_local_date, 'America/Lima')->startOfDay();

        if (! $patient || $patient->trashed() || $patient->status !== Patient::STATUS_ACTIVE) {
            return ReminderEvaluation::omit(ReminderReasonCode::PatientInactive);
        }
        if (! preg_match('/^\+51[0-9]{9}$/D', (string) $patient->whatsapp_phone)) {
            return ReminderEvaluation::omit(ReminderReasonCode::InvalidPhone);
        }
        if (! $patient->whatsapp_consented_on) {
            return ReminderEvaluation::omit(ReminderReasonCode::NoConsent);
        }
        if (! $plan || $plan->trashed() || $plan->status !== Plan::STATUS_ACTIVE) {
            return ReminderEvaluation::omit(ReminderReasonCode::PlanNotActive);
        }
        if ($date->lt($plan->starts_on) || $date->gt($plan->ends_on)) {
            return ReminderEvaluation::omit(ReminderReasonCode::OutsidePlanRange);
        }

        $schedule = $execution->schedule;
        $configuration = $schedule?->configuration;
        if (! $schedule || $schedule->trashed() || ! $configuration || ! $configuration->is_active) {
            return ReminderEvaluation::omit(ReminderReasonCode::RemindersPaused);
        }

        $routines = $plan->routines()->orderBy('starts_on')->orderBy('id')->get();
        if ($routines->isEmpty()) {
            return ReminderEvaluation::omit(ReminderReasonCode::IncompleteRoutineCoverage);
        }

        $current = $routines->filter(fn ($routine) => $routine->starts_on->lte($date) && $routine->ends_on->gte($date))->values();
        if ($current->isEmpty()) {
            return ReminderEvaluation::omit(ReminderReasonCode::NoCurrentRoutine);
        }
        if ($current->count() > 1) {
            return ReminderEvaluation::omit(ReminderReasonCode::MultipleCurrentRoutines);
        }

        $previous = null;
        foreach ($routines as $routine) {
            if ($routine->starts_on->lt($plan->starts_on) || $routine->ends_on->gt($plan->ends_on)) {
                return ReminderEvaluation::omit(ReminderReasonCode::IncompleteRoutineCoverage);
            }
            if ($previous && $routine->starts_on->gt($previous->ends_on->copy()->addDay())) {
                return ReminderEvaluation::omit(ReminderReasonCode::IncompleteRoutineCoverage);
            }
            $previous = $routine;
        }
        if (! $routines->first()->starts_on->equalTo($plan->starts_on) || ! $routines->last()->ends_on->equalTo($plan->ends_on)) {
            return ReminderEvaluation::omit(ReminderReasonCode::IncompleteRoutineCoverage);
        }

        $previous = null;
        foreach ($routines as $routine) {
            if ($previous && $routine->starts_on->lte($previous->ends_on)) {
                return ReminderEvaluation::omit(ReminderReasonCode::OverlappingRoutines);
            }
            $previous = $routine;
        }

        $routine = $current->first();
        if (! $routine->exercises()->exists()) {
            return ReminderEvaluation::omit(ReminderReasonCode::RoutineWithoutExercises, $routine);
        }

        $publicLink = $plan->publicLinks()->whereNull('revoked_at')->first();
        if (! $publicLink) {
            return ReminderEvaluation::omit(ReminderReasonCode::PublicLinkUnavailable, $routine);
        }

        try {
            $url = $this->composePublicRoutineUrl->handle($plan);
        } catch (Throwable) {
            return ReminderEvaluation::omit(ReminderReasonCode::PublicLinkUnavailable, $routine, $publicLink);
        }

        return ReminderEvaluation::send($routine, $publicLink, $url);
    }
}
