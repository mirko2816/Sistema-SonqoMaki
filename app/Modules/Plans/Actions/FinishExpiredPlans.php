<?php

namespace App\Modules\Plans\Actions;

use App\Models\Plan;
use Carbon\CarbonImmutable;

class FinishExpiredPlans
{
    public function handle(?CarbonImmutable $today = null): int
    {
        $today ??= CarbonImmutable::now('America/Lima')->startOfDay();

        return Plan::query()->where('status', Plan::STATUS_ACTIVE)->whereDate('ends_on', '<', $today->toDateString())->update(['status' => Plan::STATUS_FINISHED, 'updated_at' => now()]);
    }
}
