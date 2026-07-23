<?php

namespace App\Modules\Plans\Actions;

use App\Models\Plan;
use App\Modules\Plans\Support\PlanActivationValidator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangePlanStatus
{
    public function __construct(private PlanActivationValidator $validator) {}

    public function handle(Plan $plan, string $target): Plan
    {
        return DB::transaction(function () use ($plan, $target): Plan {
            $locked = Plan::query()->lockForUpdate()->findOrFail($plan->id);
            $locked->routines()->lockForUpdate()->get();
            $allowed = [Plan::STATUS_PAUSED => [Plan::STATUS_ACTIVE, Plan::STATUS_FINISHED], Plan::STATUS_ACTIVE => [Plan::STATUS_PAUSED, Plan::STATUS_FINISHED], Plan::STATUS_FINISHED => []];
            if (! in_array($target, $allowed[$locked->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'La transición de estado solicitada no está permitida.']);
            }
            if ($target === Plan::STATUS_ACTIVE) {
                if (($problems = $this->validator->problems($locked)) !== []) {
                    throw ValidationException::withMessages(['status' => $problems]);
                }
                if (! $locked->publicLinks()->whereNull('revoked_at')->lockForUpdate()->exists()) {
                    $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
                    $locked->publicLinks()->create(['token_hash' => hash('sha256', $token), 'token_ciphertext' => Crypt::encryptString($token), 'token_prefix' => substr($token, 0, 10)]);
                    unset($token);
                }
            }
            $locked->update(['status' => $target]);

            return $locked->fresh();
        });
    }
}
