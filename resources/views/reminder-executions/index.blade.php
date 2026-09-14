<x-layouts.authenticated title="Historial técnico">
    <x-page-header title="Historial técnico de recordatorios" description="Consulta el resultado inmediato de cada ejecución programada. Una aceptación no confirma entrega ni lectura." />

    <form method="GET" action="{{ route('reminder-executions.index') }}" class="mt-8 grid gap-4 rounded-2xl border border-ink-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5 lg:items-end sm:p-5">
        <div>
            <label for="outcome" class="block text-sm font-semibold text-ink-800">Resultado técnico</label>
            <select id="outcome" name="outcome" class="mt-2 min-h-11 w-full rounded-xl border border-ink-300 bg-white px-3 py-2 text-ink-950 shadow-sm">
                <option value="">Todos</option>
                @foreach($outcomes as $outcome)
                    <option value="{{ $outcome->value }}" @selected(($filters['outcome'] ?? null) === $outcome->value)>{{ $outcome->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="patient" class="block text-sm font-semibold text-ink-800">Paciente</label>
            <select id="patient" name="patient" class="mt-2 min-h-11 w-full rounded-xl border border-ink-300 bg-white px-3 py-2 text-ink-950 shadow-sm">
                <option value="">Todos</option>
                @foreach($patients as $patient)
                    <option value="{{ $patient->patient_id }}" @selected((string) ($filters['patient'] ?? '') === (string) $patient->patient_id)>{{ $patient->label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="plan" class="block text-sm font-semibold text-ink-800">Plan</label>
            <select id="plan" name="plan" class="mt-2 min-h-11 w-full rounded-xl border border-ink-300 bg-white px-3 py-2 text-ink-950 shadow-sm">
                <option value="">Todos</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->plan_id }}" @selected((string) ($filters['plan'] ?? '') === (string) $plan->plan_id)>{{ $plan->label }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3 lg:col-span-2">
            <div>
                <label for="date_from" class="block text-sm font-semibold text-ink-800">Desde</label>
                <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="mt-2 min-h-11 w-full rounded-xl border border-ink-300 bg-white px-3 py-2 text-ink-950 shadow-sm">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-semibold text-ink-800">Hasta</label>
                <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="mt-2 min-h-11 w-full rounded-xl border border-ink-300 bg-white px-3 py-2 text-ink-950 shadow-sm">
            </div>
        </div>
        <div class="flex gap-2 sm:col-span-2 lg:col-span-5 lg:justify-end">
            <button type="submit" class="min-h-11 rounded-xl bg-ink-900 px-5 py-2 text-sm font-semibold text-white hover:bg-ink-700">Aplicar filtros</button>
            @if(array_filter($filters, fn ($value) => $value !== null && $value !== ''))
                <a href="{{ route('reminder-executions.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-ink-300 px-5 py-2 text-sm font-semibold text-ink-700 hover:bg-ink-100">Limpiar</a>
            @endif
        </div>
    </form>

    <div class="mt-6">
        @if($executions->isEmpty())
            <x-card>
                <x-empty-state
                    :title="array_filter($filters, fn ($value) => $value !== null && $value !== '') ? 'No hay resultados para estos filtros' : 'Todavía no hay ejecuciones'"
                    :description="array_filter($filters, fn ($value) => $value !== null && $value !== '') ? 'Ajusta el resultado, paciente, plan o intervalo de fechas para ampliar la búsqueda.' : 'Las ejecuciones aparecerán aquí cuando se procesen recordatorios programados.'"
                >
                    @if(array_filter($filters, fn ($value) => $value !== null && $value !== ''))
                        <x-slot:action><a href="{{ route('reminder-executions.index') }}" class="inline-flex min-h-11 items-center rounded-xl border border-ink-300 px-4 py-2 text-sm font-semibold text-ink-700">Limpiar filtros</a></x-slot:action>
                    @endif
                </x-empty-state>
            </x-card>
        @else
            <div class="grid gap-4 md:hidden">
                @foreach($executions as $execution)
                    <article class="rounded-2xl border border-ink-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div><p class="font-semibold text-ink-950">{{ $execution->patient_name_snapshot }}</p><p class="mt-1 text-sm text-ink-600">{{ $execution->plan_name_snapshot }}</p></div>
                            <x-reminder-outcome :outcome="$execution->outcome" />
                        </div>
                        <dl class="mt-4 space-y-2 text-sm">
                            <div><dt class="inline font-semibold text-ink-700">Programado: </dt><dd class="inline text-ink-600">{{ $execution->scheduled_local_date->format('d/m/Y') }} · {{ substr($execution->scheduled_local_time, 0, 5) }} (America/Lima)</dd></div>
                            <div><dt class="inline font-semibold text-ink-700">Recordatorio: </dt><dd class="inline text-ink-600">{{ implode(' · ', array_filter([$execution->schedule ? 'Horario '.substr($execution->schedule->send_at, 0, 5) : null, $execution->routine?->name])) ?: 'No disponible' }}</dd></div>
                            @if($execution->reason_code)<div><dt class="inline font-semibold text-ink-700">Motivo: </dt><dd class="inline text-ink-600">{{ $execution->reason_code->label() }}</dd></div>@endif
                        </dl>
                        <a href="{{ route('reminder-executions.show', $execution) }}" class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-brand-200 bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-800">Ver detalle técnico</a>
                    </article>
                @endforeach
            </div>

            <x-table-container class="hidden md:block">
                <table class="w-full text-left">
                    <thead class="border-b border-brand-300 bg-secondary text-xs uppercase tracking-wide text-ink-900"><tr><th class="px-5 py-3">Programado</th><th class="px-5 py-3">Paciente y plan</th><th class="px-5 py-3">Recordatorio</th><th class="px-5 py-3">Resultado</th><th class="px-5 py-3 text-right">Acción</th></tr></thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach($executions as $execution)
                            <tr>
                                <td class="px-5 py-4 text-sm text-ink-700"><span class="font-semibold text-ink-950">{{ $execution->scheduled_local_date->format('d/m/Y') }}</span><br>{{ substr($execution->scheduled_local_time, 0, 5) }} · Lima</td>
                                <td class="px-5 py-4"><span class="font-semibold text-ink-950">{{ $execution->patient_name_snapshot }}</span><br><span class="text-sm text-ink-600">{{ $execution->plan_name_snapshot }}</span></td>
                                <td class="px-5 py-4 text-sm text-ink-700">{{ implode(' · ', array_filter([$execution->schedule ? 'Horario '.substr($execution->schedule->send_at, 0, 5) : null, $execution->routine?->name])) ?: 'No disponible' }}</td>
                                <td class="px-5 py-4"><x-reminder-outcome :outcome="$execution->outcome" />@if($execution->reason_code)<p class="mt-2 max-w-xs text-xs text-ink-600">{{ $execution->reason_code->label() }}</p>@endif</td>
                                <td class="px-5 py-4 text-right"><a href="{{ route('reminder-executions.show', $execution) }}" class="font-semibold text-brand-700 hover:text-brand-900">Ver detalle</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-container>
            <div class="mt-5">{{ $executions->links() }}</div>
        @endif
    </div>
</x-layouts.authenticated>
