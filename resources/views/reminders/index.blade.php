<x-layouts.authenticated title="Recordatorios">
    <x-page-header title="Recordatorios" description="Consulta y configura los horarios de cada plan de forma independiente.">
        <x-slot:actions><a href="{{ route('reminder-executions.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Ver historial técnico</a></x-slot:actions>
    </x-page-header>

    <x-alert class="mt-6">
        Todos los horarios usan la zona fija <strong>America/Lima</strong>. Configurarlos o pausarlos no cambia el estado del plan.
    </x-alert>

    <div class="mt-8">
        <x-card>
            <div class="hidden border-b border-slate-200 bg-slate-50 px-6 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 md:grid md:grid-cols-[1.2fr_1.2fr_0.8fr_1fr_auto] md:gap-4">
                <span>Paciente</span><span>Plan</span><span>Plan</span><span>Recordatorios</span><span>Acción</span>
            </div>
            <div class="divide-y divide-slate-200">
                @forelse($plans as $plan)
                    @php($configuration = $plan->reminderConfiguration)
                    @php($scheduleCount = $configuration?->schedules_count ?? 0)
                    <article class="grid gap-3 p-5 text-sm md:grid-cols-[1.2fr_1.2fr_0.8fr_1fr_auto] md:items-center md:gap-4 md:px-6">
                        <div><span class="font-semibold md:hidden">Paciente: </span>{{ $plan->patient->full_name }}</div>
                        <div class="font-semibold">{{ $plan->name }}</div>
                        <div><span class="font-semibold md:hidden">Estado del plan: </span>{{ ['active' => 'Activo', 'paused' => 'En pausa', 'finished' => 'Finalizado'][$plan->status] }}</div>
                        <div>
                            @if($scheduleCount === 0)
                                <span class="text-slate-600">Sin horarios</span>
                            @elseif($configuration->is_active)
                                <span class="font-semibold text-emerald-700">Activos · {{ $scheduleCount }} horario(s)</span>
                            @else
                                <span class="font-semibold text-amber-800">Configurados y pausados · {{ $scheduleCount }}</span>
                            @endif
                        </div>
                        <a href="{{ route('reminders.edit', $plan) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-brand-300 px-4 font-semibold text-brand-800">Configurar</a>
                    </article>
                @empty
                    <div class="p-6"><x-empty-state title="Todavía no existen planes" description="Crea un plan para configurar sus recordatorios." /></div>
                @endforelse
            </div>
        </x-card>
        <div class="mt-6">{{ $plans->links() }}</div>
    </div>
</x-layouts.authenticated>
