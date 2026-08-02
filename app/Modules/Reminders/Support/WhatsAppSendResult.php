<?php

namespace App\Modules\Reminders\Support;

final readonly class WhatsAppSendResult
{
    private function __construct(
        public bool $accepted,
        public ?string $messageId,
        public ?int $httpStatus,
        public ?string $errorCode,
        public ?string $errorDetail,
    ) {}

    public static function accepted(string $messageId, int $httpStatus): self
    {
        return new self(true, $messageId, $httpStatus, null, null);
    }

    public static function failed(?int $httpStatus, string $errorCode, string $errorDetail): self
    {
        return new self(false, null, $httpStatus, $errorCode, $errorDetail);
    }
}
