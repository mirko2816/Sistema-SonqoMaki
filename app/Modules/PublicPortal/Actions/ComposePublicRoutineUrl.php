<?php

namespace App\Modules\PublicPortal\Actions;

use App\Models\Plan;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use LogicException;
use RuntimeException;

class ComposePublicRoutineUrl
{
    public function handle(Plan $plan): string
    {
        if ($plan->trashed() || ! $plan->patient || $plan->patient->trashed()) {
            throw new LogicException('El plan no tiene un enlace público disponible.');
        }

        $link = $plan->publicLinks()->whereNull('revoked_at')->first();

        if (! $link) {
            throw new LogicException('El plan no tiene un enlace público disponible.');
        }

        try {
            $token = Crypt::decryptString($link->token_ciphertext);
        } catch (DecryptException) {
            throw new RuntimeException('El enlace público no se pudo recuperar de forma segura.');
        }

        if (! hash_equals($link->token_hash, hash('sha256', $token))) {
            throw new RuntimeException('El enlace público no superó la comprobación de integridad.');
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        $scheme = parse_url($baseUrl, PHP_URL_SCHEME);

        if (! filter_var($baseUrl, FILTER_VALIDATE_URL) || ! in_array($scheme, ['http', 'https'], true)) {
            throw new LogicException('APP_URL debe contener una URL HTTP o HTTPS válida.');
        }

        return $baseUrl.route('public-routine.show', ['token' => $token], false);
    }
}
