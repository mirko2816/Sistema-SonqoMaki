<?php

namespace App\Modules\Plans\Actions;

use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class ArchivePlan
{
    public function handle(Plan $plan): void
    {
        DB::transaction(function () use ($plan): void {
            $locked = Plan::query()->lockForUpdate()->findOrFail($plan->id);
            $locked->update(['status' => Plan::STATUS_PAUSED]);
            $locked->publicLinks()->whereNull('revoked_at')->update(['revoked_at' => now(), 'updated_at' => now()]);
            $locked->delete();
        });
    }
}
