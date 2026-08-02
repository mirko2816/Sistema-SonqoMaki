<?php

namespace App\Modules\Reminders\Infrastructure;

use App\Modules\Reminders\Support\ReminderMessage;

class WhatsAppTemplatePayloadBuilder
{
    public function build(ReminderMessage $message, string $templateName, string $language): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => ltrim($message->recipientPhone, '+'),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $language],
                'components' => [[
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $message->patientName],
                        ['type' => 'text', 'text' => $message->publicUrl],
                    ],
                ]],
            ],
        ];
    }
}
