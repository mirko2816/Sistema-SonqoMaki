<?php

namespace App\Http\Controllers;

use App\Modules\PublicPortal\Actions\DecidePublicRoutine;
use App\Modules\PublicPortal\Actions\ResolvePublicLink;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class PublicRoutineController extends Controller
{
    public function __invoke(
        string $token,
        ResolvePublicLink $resolvePublicLink,
        DecidePublicRoutine $decidePublicRoutine,
    ): View {
        try {
            $result = $decidePublicRoutine->handle($resolvePublicLink->handle($token));
        } catch (Throwable $exception) {
            Log::error('No se pudo resolver una consulta del portal público.', [
                'exception_type' => $exception::class,
            ]);

            $result = $decidePublicRoutine->unavailable();
        }

        return view('public-routine.show', compact('result'));
    }
}
