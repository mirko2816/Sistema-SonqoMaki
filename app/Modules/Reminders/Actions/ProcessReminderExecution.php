<?php

namespace App\Modules\Reminders\Actions;

use App\Models\ReminderExecution;
use App\Modules\Reminders\Contracts\WhatsAppProvider;
use App\Modules\Reminders\Enums\ReminderOutcome;
use App\Modules\Reminders\Enums\ReminderReasonCode;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessReminderExecution
{
    public function __construct(
        private readonly EvaluateReminderExecution $evaluate,
        private readonly ComposeReminderMessage $compose,
        private readonly WhatsAppProvider $provider,
    ) {}

    public function handle(ReminderExecution $execution): ReminderExecution
    {
        $started = hrtime(true);

        try {
            $evaluation = $this->evaluate->handle($execution);

            if (! $evaluation->shouldSend()) {
                $execution->update([
                    'routine_id' => $evaluation->routine?->getKey(),
                    'public_link_id' => $evaluation->publicLink?->getKey(),
                    'outcome' => ReminderOutcome::Omitted,
                    'reason_code' => $evaluation->reason,
                    'completed_at' => now('UTC'),
                    'duration_ms' => $this->duration($started),
                ]);

                Log::info('Reminder execution omitted.', $this->logContext($execution));

                return $execution->fresh();
            }

            $execution->update([
                'routine_id' => $evaluation->routine->getKey(),
                'public_link_id' => $evaluation->publicLink->getKey(),
            ]);

            $result = $this->provider->send($this->compose->handle($execution, $evaluation->publicUrl));

            if ($result->accepted) {
                $execution->update([
                    'outcome' => ReminderOutcome::Accepted,
                    'whatsapp_message_id' => $result->messageId,
                    'provider_http_status' => $result->httpStatus,
                    'completed_at' => now('UTC'),
                    'duration_ms' => $this->duration($started),
                ]);
            } else {
                $execution->update([
                    'outcome' => ReminderOutcome::Failed,
                    'reason_code' => ReminderReasonCode::WhatsappError,
                    'provider_http_status' => $result->httpStatus,
                    'provider_error_code' => $result->errorCode,
                    'provider_error_detail' => $result->errorDetail,
                    'completed_at' => now('UTC'),
                    'duration_ms' => $this->duration($started),
                ]);
            }

            Log::info('Reminder execution completed.', $this->logContext($execution));

            return $execution->fresh();
        } catch (Throwable $exception) {
            $execution->update([
                'outcome' => ReminderOutcome::Failed,
                'reason_code' => ReminderReasonCode::UnexpectedError,
                'whatsapp_message_id' => null,
                'provider_http_status' => null,
                'provider_error_code' => 'UNEXPECTED_ERROR',
                'provider_error_detail' => 'Ocurrió un error interno inesperado.',
                'completed_at' => now('UTC'),
                'duration_ms' => $this->duration($started),
            ]);

            Log::error('Unexpected reminder execution error.', $this->logContext($execution) + [
                'exception_class' => $exception::class,
            ]);

            return $execution->fresh();
        }
    }

    private function duration(int $started): int
    {
        return max(0, (int) round((hrtime(true) - $started) / 1_000_000));
    }

    private function logContext(ReminderExecution $execution): array
    {
        return [
            'correlation_id' => $execution->correlation_id,
            'plan_id' => $execution->plan_id,
            'reminder_schedule_id' => $execution->reminder_schedule_id,
            'outcome' => $execution->outcome instanceof ReminderOutcome ? $execution->outcome->value : $execution->outcome,
            'reason_code' => $execution->reason_code instanceof ReminderReasonCode ? $execution->reason_code->value : $execution->reason_code,
        ];
    }
}
