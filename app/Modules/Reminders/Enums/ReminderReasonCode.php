<?php

namespace App\Modules\Reminders\Enums;

enum ReminderReasonCode: string
{
    case PatientInactive = 'PATIENT_INACTIVE';
    case InvalidPhone = 'INVALID_PHONE';
    case NoConsent = 'NO_CONSENT';
    case PlanNotActive = 'PLAN_NOT_ACTIVE';
    case OutsidePlanRange = 'OUTSIDE_PLAN_RANGE';
    case RemindersPaused = 'REMINDERS_PAUSED';
    case IncompleteRoutineCoverage = 'INCOMPLETE_ROUTINE_COVERAGE';
    case OverlappingRoutines = 'OVERLAPPING_ROUTINES';
    case NoCurrentRoutine = 'NO_CURRENT_ROUTINE';
    case MultipleCurrentRoutines = 'MULTIPLE_CURRENT_ROUTINES';
    case RoutineWithoutExercises = 'ROUTINE_WITHOUT_EXERCISES';
    case PublicLinkUnavailable = 'PUBLIC_LINK_UNAVAILABLE';
    case WhatsappError = 'WHATSAPP_ERROR';
    case UnexpectedError = 'UNEXPECTED_ERROR';

    public function label(): string
    {
        return match ($this) {
            self::PatientInactive => 'Paciente inactivo',
            self::InvalidPhone => 'Teléfono no válido',
            self::NoConsent => 'Sin consentimiento para WhatsApp',
            self::PlanNotActive => 'Plan no activo',
            self::OutsidePlanRange => 'Fecha fuera del rango del plan',
            self::RemindersPaused => 'Recordatorios en pausa',
            self::IncompleteRoutineCoverage => 'Cobertura de rutinas incompleta',
            self::OverlappingRoutines => 'Rutinas superpuestas',
            self::NoCurrentRoutine => 'Sin rutina vigente',
            self::MultipleCurrentRoutines => 'Varias rutinas vigentes',
            self::RoutineWithoutExercises => 'Rutina sin ejercicios',
            self::PublicLinkUnavailable => 'Enlace público no disponible',
            self::WhatsappError => 'Error de WhatsApp',
            self::UnexpectedError => 'Error interno inesperado',
        };
    }
}
