<?php

namespace App\Modules\Plans\Actions;

use App\Models\Plan;
use App\Modules\Plans\Support\PlanActivationValidator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdatePlan
{
    public function __construct(private PlanActivationValidator $validator) {}

    public function handle(Plan $plan, array $data): Plan
    {
        return DB::transaction(function () use ($plan, $data): Plan {
            $locked = Plan::query()->lockForUpdate()->findOrFail($plan->id);
            if (! $locked->updated_at->equalTo(CarbonImmutable::parse($data['updated_at']))) {
                throw ValidationException::withMessages(['updated_at' => 'Este plan cambió desde que abriste el formulario. Recarga la página.']);
            }
            $locked->routines()->lockForUpdate()->get();
            $locked->update(['name' => $data['name'], 'starts_on' => $data['starts_on'], 'ends_on' => $data['ends_on']]);
            $outside = $locked->routines()->where(fn ($q) => $q->where('starts_on', '<', $locked->starts_on)->orWhere('ends_on', '>', $locked->ends_on))->exists();
            if ($outside) {
                throw ValidationException::withMessages(['starts_on' => 'El nuevo rango dejaría una rutina fuera del plan. Ajusta primero las rutinas.']);
            }
            if ($locked->status === Plan::STATUS_ACTIVE && ($problems = $this->validator->problems($locked->fresh())) !== []) {
                throw ValidationException::withMessages(['plan' => $problems]);
            }

            return $locked->fresh();
        });
    }
}
