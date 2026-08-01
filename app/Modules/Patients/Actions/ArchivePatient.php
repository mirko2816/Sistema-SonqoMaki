<?php

namespace App\Modules\Patients\Actions;

use App\Models\Patient;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class ArchivePatient
{
    public function handle(Patient $patient): void
    {
        DB::transaction(function () use ($patient): void {
            $lockedPatient = Patient::query()->lockForUpdate()->findOrFail($patient->getKey());
            $lockedPatient->update(['status' => Patient::STATUS_INACTIVE]);

            foreach ($lockedPatient->plans()->lockForUpdate()->get() as $plan) {
                if ($plan->status !== Plan::STATUS_FINISHED) {
                    $plan->update(['status' => Plan::STATUS_PAUSED]);
                }
                $plan->publicLinks()->whereNull('revoked_at')->update(['revoked_at' => now(), 'updated_at' => now()]);
            }

            $lockedPatient->delete();
        });
    }
}
