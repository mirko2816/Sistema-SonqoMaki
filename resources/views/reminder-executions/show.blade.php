<x-layouts.authenticated title="Detalle de ejecución">
    <x-page-header title="Detalle de ejecución" description="Información técnica de solo lectura conservada para diagnóstico.">
        <x-slot:actions><a href="{{ route('reminder-executions.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-ink-300 bg-white px-5 py-2.5 text-sm font-semibold text-ink-700 hover:bg-ink-100">Volver al historial</a></x-slot:actions>
    </x-page-header>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <x-card class="p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3"><h2 class="text-lg font-semibold text-ink-950">Ejecución programada</h2><x-reminder-outcome :outcome="$execution->outcome" /></div>
            <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Fecha y hora</dt><dd class="mt-1 text-sm text-ink-900">{{ $execution->scheduled_local_date->format('d/m/Y') }} · {{ substr($execution->scheduled_local_time, 0, 5) }} (America/Lima)</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Paciente</dt><dd class="mt-1 text-sm text-ink-900">{{ $execution->patient_name_snapshot }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Plan</dt><dd class="mt-1 text-sm text-ink-900">{{ $execution->plan_name_snapshot }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Recordatorio relacionado</dt><dd class="mt-1 text-sm text-ink-900">{{ implode(' · ', array_filter([$execution->schedule ? 'Horario '.substr($execution->schedule->send_at, 0, 5) : null, $execution->routine?->name])) ?: 'No disponible' }}</dd></div>
                @if($execution->reason_code)<div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">{{ $execution->outcome === \App\Modules\Reminders\Enums\ReminderOutcome::Omitted ? 'Motivo de omisión' : 'Motivo técnico' }}</dt><dd class="mt-1 text-sm text-ink-900">{{ $execution->reason_code->label() }}</dd></div>@endif
            </dl>
        </x-card>

        <x-card class="p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-ink-950">Trazabilidad técnica</h2>
            <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Identificador de correlación</dt><dd class="mt-1 break-all font-mono text-sm text-ink-900">{{ $execution->correlation_id }}</dd></div>
                @if($execution->whatsapp_message_id)<div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Identificador de WhatsApp</dt><dd class="mt-1 break-all font-mono text-sm text-ink-900">{{ $execution->whatsapp_message_id }}</dd></div>@endif
                @if($execution->provider_http_status)<div><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Código HTTP</dt><dd class="mt-1 text-sm text-ink-900">{{ $execution->provider_http_status }}</dd></div>@endif
                @if($execution->provider_error_code)<div><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Código de error</dt><dd class="mt-1 break-all font-mono text-sm text-ink-900">{{ $execution->provider_error_code }}</dd></div>@endif
                @if($safeErrorDetail)<div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Detalle sanitizado del error</dt><dd class="mt-1 whitespace-pre-wrap break-words text-sm text-ink-900">{{ $safeErrorDetail }}</dd></div>@endif
                @if($execution->duration_ms !== null)<div><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Duración</dt><dd class="mt-1 text-sm text-ink-900">{{ $execution->duration_ms }} ms</dd></div>@endif
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Inicio</dt><dd class="mt-1 text-sm text-ink-900">{{ $execution->started_at->setTimezone('America/Lima')->format('d/m/Y H:i:s') }} (Lima)</dd></div>
                @if($execution->completed_at)<div><dt class="text-xs font-semibold uppercase tracking-wide text-ink-500">Finalización</dt><dd class="mt-1 text-sm text-ink-900">{{ $execution->completed_at->setTimezone('America/Lima')->format('d/m/Y H:i:s') }} (Lima)</dd></div>@endif
            </dl>
        </x-card>
    </div>
</x-layouts.authenticated>
