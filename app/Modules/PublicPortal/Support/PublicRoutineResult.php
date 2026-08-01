<?php

namespace App\Modules\PublicPortal\Support;

use App\Models\Routine;

final readonly class PublicRoutineResult
{
    public const AVAILABLE = 'available';

    public const PAUSED = 'paused';

    public const FINISHED = 'finished';

    public const UNAVAILABLE = 'unavailable';

    public function __construct(
        public string $state,
        public ?Routine $routine = null,
    ) {}
}
