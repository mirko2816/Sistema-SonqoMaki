<?php

namespace App\Modules\Plans\Support;

use App\Models\Patient;
use App\Models\Plan;
use Carbon\CarbonImmutable;

class PlanActivationValidator
{
    /** @return list<string> */
    public function problems(Plan $plan): array
    {
        $plan->loadMissing(['patient', 'routines.exercises', 'currentPublicLink']);
        $problems = [];

        if (! $plan->patient || $plan->patient->trashed()) {
            $problems[] = 'El paciente no existe o está archivado.';
        } elseif ($plan->patient->status !== Patient::STATUS_ACTIVE) {
            $problems[] = 'El paciente debe estar activo.';
        }
        if ($plan->status === Plan::STATUS_FINISHED) {
            $problems[] = 'Un plan finalizado no puede reactivarse.';
        }
        if (! $plan->starts_on || ! $plan->ends_on || $plan->starts_on->gt($plan->ends_on)) {
            $problems[] = 'El rango de fechas del plan no es válido.';
        }
        if ($plan->ends_on && $plan->ends_on->lt(CarbonImmutable::now('America/Lima')->startOfDay())) {
            $problems[] = 'El plan está vencido y no puede activarse.';
        }

        $routines = $plan->routines->sortBy(fn ($routine) => [$routine->starts_on->format('Y-m-d'), $routine->id])->values();
        if ($routines->isEmpty()) {
            $problems[] = 'Agrega al menos una rutina.';

            return $problems;
        }

        $expected = $plan->starts_on?->toImmutable();
        foreach ($routines as $index => $routine) {
            if ($routine->starts_on->lt($plan->starts_on) || $routine->ends_on->gt($plan->ends_on) || $routine->starts_on->gt($routine->ends_on)) {
                $problems[] = "La rutina «{$routine->name}» queda fuera del rango del plan.";
            }
            if ($expected && ! $routine->starts_on->equalTo($expected)) {
                $previous = $index > 0 ? $routines[$index - 1] : null;
                $problems[] = $previous && $routine->starts_on->lte($previous->ends_on)
                    ? "Las rutinas «{$previous->name}» y «{$routine->name}» se superponen."
                    : 'Las rutinas dejan uno o más días sin cobertura.';
            }
            if ($routine->exercises->isEmpty()) {
                $problems[] = "La rutina «{$routine->name}» no tiene ejercicios.";
            }
            $positions = $routine->exercises->pluck('position')->sort()->values()->all();
            $expectedPositions = $routine->exercises->isEmpty() ? [] : range(1, $routine->exercises->count());
            if ($positions !== $expectedPositions) {
                $problems[] = "El orden de ejercicios de «{$routine->name}» no es contiguo.";
            }
            $expected = CarbonImmutable::parse($routine->ends_on)->addDay();
        }
        if ($expected && ! $expected->equalTo($plan->ends_on->toImmutable()->addDay())) {
            $problems[] = 'Las rutinas no cubren la fecha final del plan.';
        }

        return array_values(array_unique($problems));
    }
}
