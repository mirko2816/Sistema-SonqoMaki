<?php

namespace App\Modules\PublicPortal\Actions;

use App\Models\Plan;
use App\Models\PublicLink;

class ResolvePublicLink
{
    public function handle(string $token): ?Plan
    {
        if (! preg_match('/\A[A-Za-z0-9_-]{43,128}\z/D', $token)) {
            return null;
        }

        $link = PublicLink::query()
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('revoked_at')
            ->with([
                'plan' => fn ($query) => $query->withTrashed()->with([
                    'patient' => fn ($query) => $query->withTrashed(),
                ]),
            ])
            ->first();

        $plan = $link?->plan;

        if (! $plan || $plan->trashed() || ! $plan->patient || $plan->patient->trashed()) {
            return null;
        }

        return $plan;
    }
}
