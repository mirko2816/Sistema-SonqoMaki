<?php

namespace App\Modules\Plans\Actions;

use App\Models\Plan;
use App\Models\Routine;
use App\Modules\Plans\Support\PlanActivationValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteRoutine
{
    public function __construct(private PlanActivationValidator $validator) {}

    public function handle(Plan $plan, Routine $routine): void
    {
        DB::transaction(function () use ($plan, $routine): void {
            $lockedPlan = Plan::query()->lockForUpdate()->findOrFail($plan->id);
            $item = Routine::query()->where('plan_id', $lockedPlan->id)->lockForUpdate()->findOrFail($routine->id);
            $item->delete();
            if ($lockedPlan->status === Plan::STATUS_ACTIVE && ($problems = $this->validator->problems($lockedPlan->fresh())) !== []) {
                throw ValidationException::withMessages(['routine' => $problems]);
            }
        });
    }
}
