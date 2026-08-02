<?php

namespace App\Modules\Reminders\Actions;

use App\Models\ReminderExecution;
use App\Modules\Reminders\Support\ReminderMessage;

class ComposeReminderMessage
{
    public function handle(ReminderExecution $execution, string $publicUrl): ReminderMessage
    {
        $name = $execution->patient_name_snapshot;

        return new ReminderMessage(
            recipientPhone: $execution->recipient_phone_snapshot,
            patientName: $name,
            publicUrl: $publicUrl,
            text: "Hola {$name}. Tu salud es importante. Recuerda realizar tu rutina de hoy: {$publicUrl}.",
        );
    }
}
