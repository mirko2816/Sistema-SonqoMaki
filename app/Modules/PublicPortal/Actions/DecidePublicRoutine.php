<?php

namespace App\Modules\PublicPortal\Actions;

use App\Models\Plan;
use App\Modules\PublicPortal\Support\PublicRoutineResult;
use Carbon\CarbonImmutable;

class DecidePublicRoutine
{
    public function handle(?Plan $plan, ?CarbonImmutable $today = null): PublicRoutineResult
    {
        if (! $plan) {
            return $this->unavailable();
        }

        $today ??= CarbonImmutable::now('America/Lima')->startOfDay();

        if ($plan->status === Plan::STATUS_PAUSED) {
            return new PublicRoutineResult(PublicRoutineResult::PAUSED);
        }

        if ($plan->status === Plan::STATUS_FINISHED || $today->gt($plan->ends_on)) {
            return new PublicRoutineResult(PublicRoutineResult::FINISHED);
        }

        if (
            $plan->status !== Plan::STATUS_ACTIVE
            || $today->lt($plan->starts_on)
            || $today->gt($plan->ends_on)
        ) {
            return $this->unavailable();
        }

        $routines = $plan->routines()
            ->whereDate('starts_on', '<=', $today->toDateString())
            ->whereDate('ends_on', '>=', $today->toDateString())
            ->with('exercises')
            ->limit(2)
            ->get();

        if ($routines->count() !== 1 || $routines->first()->exercises->isEmpty()) {
            return $this->unavailable();
        }

        return new PublicRoutineResult(PublicRoutineResult::AVAILABLE, $routines->first());
    }

    public function unavailable(): PublicRoutineResult
    {
        return new PublicRoutineResult(PublicRoutineResult::UNAVAILABLE);
    }
}
