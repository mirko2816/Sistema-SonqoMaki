<?php

namespace App\Modules\Reminders\Support;

use App\Models\PublicLink;
use App\Models\Routine;
use App\Modules\Reminders\Enums\ReminderReasonCode;

final readonly class ReminderEvaluation
{
    private function __construct(
        public ?ReminderReasonCode $reason,
        public ?Routine $routine = null,
        public ?PublicLink $publicLink = null,
        public ?string $publicUrl = null,
    ) {}

    public static function omit(ReminderReasonCode $reason, ?Routine $routine = null, ?PublicLink $publicLink = null): self
    {
        return new self($reason, $routine, $publicLink);
    }

    public static function send(Routine $routine, PublicLink $publicLink, string $publicUrl): self
    {
        return new self(null, $routine, $publicLink, $publicUrl);
    }

    public function shouldSend(): bool
    {
        return $this->reason === null;
    }
}
