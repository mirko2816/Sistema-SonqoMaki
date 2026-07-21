<x-layouts.authenticated title="Dashboard">
    <x-page-header
        title="Dashboard"
        description="Consulta aquí los planes activos y el estado de sus recordatorios."
    />

    <div class="mt-8">
        <x-card>
            <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
                <h2 class="text-lg font-semibold text-slate-950">Planes activos</h2>
                <p class="mt-1 text-sm text-slate-600">Cada plan aparecerá en una fila independiente.</p>
            </div>

            <div class="hidden border-b border-slate-200 bg-slate-50 px-6 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 md:grid md:grid-cols-[1.4fr_1fr_1.2fr_0.8fr_1fr] md:gap-4" aria-hidden="true">
                <span>Paciente</span>
                <span>Teléfono</span>
                <span>Plan</span>
                <span>Estado</span>
                <span>Recordatorios</span>
            </div>

            <x-empty-state
                title="Todavía no existen planes activos"
                description="Cuando se incorporen pacientes y se activen sus planes, podrás consultar aquí su información y el estado de los recordatorios."
            />
        </x-card>
    </div>

    <p class="mt-5 text-sm leading-6 text-slate-500">
        Los módulos de pacientes y ejercicios ya están disponibles. Rutinas, planes y recordatorios se habilitarán en próximas etapas.
    </p>
</x-layouts.authenticated>
