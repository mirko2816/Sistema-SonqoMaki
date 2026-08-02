<?php

namespace App\Modules\Reminders\Support;

class ProviderErrorSanitizer
{
    public function code(mixed $value, string $fallback): string
    {
        $code = strtoupper((string) $value);
        $code = preg_replace('/[^A-Z0-9_.-]/', '_', $code) ?: $fallback;

        return substr($code, 0, 100);
    }

    public function detail(mixed $value, string $fallback): string
    {
        if (! is_scalar($value)) {
            return $fallback;
        }

        $detail = (string) $value;
        $detail = preg_replace('/https?:\/\/\S+/i', '[URL_REDACTED]', $detail) ?? $fallback;
        $detail = preg_replace('/\bBearer\s+\S+/i', 'Bearer [REDACTED]', $detail) ?? $fallback;
        $detail = preg_replace('/(?i)(access[_ -]?token|token|authorization)\s*[:=]\s*\S+/', '$1=[REDACTED]', $detail) ?? $fallback;
        $detail = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $detail) ?? $fallback;
        $detail = trim($detail);

        return mb_substr($detail !== '' ? $detail : $fallback, 0, 500);
    }
}
