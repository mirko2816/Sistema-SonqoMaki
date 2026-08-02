<?php

namespace App\Modules\Reminders\Enums;

enum ReminderOutcome: string
{
    case Processing = 'processing';
    case Omitted = 'omitted';
    case Accepted = 'accepted';
    case Failed = 'failed';
}
