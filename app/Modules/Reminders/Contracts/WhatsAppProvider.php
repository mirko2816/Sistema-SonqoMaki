<?php

namespace App\Modules\Reminders\Contracts;

use App\Modules\Reminders\Support\ReminderMessage;
use App\Modules\Reminders\Support\WhatsAppSendResult;

interface WhatsAppProvider
{
    public function send(ReminderMessage $message): WhatsAppSendResult;
}
