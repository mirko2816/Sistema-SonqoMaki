<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reminders\ReminderExecutionHistoryRequest;
use App\Models\ReminderExecution;
use App\Modules\Reminders\Actions\SearchReminderExecutionHistory;
use App\Modules\Reminders\Enums\ReminderOutcome;
use App\Modules\Reminders\Support\ProviderErrorSanitizer;
use Illuminate\View\View;

class ReminderExecutionController extends Controller
{
    public function index(
        ReminderExecutionHistoryRequest $request,
        SearchReminderExecutionHistory $history,
    ): View {
        $filters = $request->validated();
        $executions = $history->handle($filters);
        $patients = $history->patientOptions();
        $plans = $history->planOptions();
        $outcomes = ReminderOutcome::cases();

        return view('reminder-executions.index', compact('executions', 'patients', 'plans', 'outcomes', 'filters'));
    }

    public function show(ReminderExecution $execution, ProviderErrorSanitizer $sanitizer): View
    {
        $execution->load([
            'schedule:id,reminder_configuration_id,weekday,send_at,deleted_at',
            'routine:id,plan_id,name,deleted_at',
        ]);
        $safeErrorDetail = $execution->provider_error_detail === null
            ? null
            : $sanitizer->detail($execution->provider_error_detail, 'Detalle técnico no disponible.');

        return view('reminder-executions.show', compact('execution', 'safeErrorDetail'));
    }
}
