<?php

namespace App\Modules\Reminders\Infrastructure;

use App\Modules\Reminders\Contracts\WhatsAppProvider;
use App\Modules\Reminders\Support\ProviderErrorSanitizer;
use App\Modules\Reminders\Support\ReminderMessage;
use App\Modules\Reminders\Support\WhatsAppSendResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class WhatsAppCloudApiProvider implements WhatsAppProvider
{
    public function __construct(
        private readonly WhatsAppTemplatePayloadBuilder $payloadBuilder,
        private readonly ProviderErrorSanitizer $sanitizer,
    ) {}

    public function send(ReminderMessage $message): WhatsAppSendResult
    {
        $settings = config('services.whatsapp', []);
        $token = (string) ($settings['access_token'] ?? '');
        $phoneNumberId = (string) ($settings['phone_number_id'] ?? '');
        $version = (string) ($settings['graph_version'] ?? '');
        $template = (string) ($settings['template_name'] ?? '');
        $language = (string) ($settings['template_language'] ?? '');

        if (
            $token === ''
            || ! preg_match('/^[0-9]+$/D', $phoneNumberId)
            || ! preg_match('/^v[0-9]+\.[0-9]+$/D', $version)
            || ! preg_match('/^[a-z0-9_]+$/D', $template)
            || ! preg_match('/^[a-z]{2,3}(?:_[A-Z]{2})?$/D', $language)
        ) {
            return WhatsAppSendResult::failed(null, 'CONFIGURATION_INCOMPLETE', 'La configuración de WhatsApp está incompleta o no es válida.');
        }

        $endpoint = "https://graph.facebook.com/{$version}/{$phoneNumberId}/messages";

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->connectTimeout((int) ($settings['connect_timeout'] ?? 5))
                ->timeout((int) ($settings['timeout'] ?? 10))
                ->post($endpoint, $this->payloadBuilder->build($message, $template, $language));
        } catch (ConnectionException) {
            return WhatsAppSendResult::failed(null, 'CONNECTION_ERROR', 'WhatsApp no respondió dentro del tiempo esperado.');
        } catch (Throwable) {
            return WhatsAppSendResult::failed(null, 'CLIENT_ERROR', 'No se pudo completar la solicitud a WhatsApp.');
        }

        $status = $response->status();
        $metaError = $response->json('error');
        if (is_array($metaError)) {
            return WhatsAppSendResult::failed(
                $status,
                $this->sanitizer->code($metaError['code'] ?? null, 'META_ERROR'),
                $this->sanitizer->detail($metaError['message'] ?? null, 'WhatsApp rechazó la solicitud.'),
            );
        }

        if (! $response->successful()) {
            return WhatsAppSendResult::failed($status, 'HTTP_'.$status, 'WhatsApp rechazó la solicitud.');
        }

        $messageId = $response->json('messages.0.id');
        if (! is_string($messageId) || $messageId === '' || mb_strlen($messageId) > 255) {
            return WhatsAppSendResult::failed($status, 'MALFORMED_RESPONSE', 'WhatsApp devolvió una respuesta inesperada.');
        }

        return WhatsAppSendResult::accepted($messageId, $status);
    }
}
