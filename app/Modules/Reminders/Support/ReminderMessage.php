<?php

namespace App\Modules\Reminders\Support;

final readonly class ReminderMessage
{
    public function __construct(
        public string $recipientPhone,
        public string $patientName,
        public string $publicUrl,
        public string $text,
    ) {}
}
