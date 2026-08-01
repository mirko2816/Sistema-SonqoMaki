<x-layouts.authenticated title="Pacientes">
    <x-page-header title="Pacientes" description="Busca, consulta y administra pacientes activos e inactivos.">
        <x-slot:actions>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('patients.archived') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Ver archivados</a>
                <a href="{{ route('patients.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Registrar paciente</a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('patients.index') }}" class="mt-8 grid gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[minmax(0,1fr)_12rem_auto] sm:items-end sm:p-5">
        <div>
            <label for="search" class="block text-sm font-semibold text-slate-800">Buscar paciente</label>
            <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Nombre, apellido, DNI o teléfono" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-950 shadow-sm">
        </div>
        <div>
            <label for="status" class="block text-sm font-semibold text-slate-800">Estado</label>
            <select id="status" name="status" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-950 shadow-sm">
                <option value="">Todos</option>
                <option value="active" @selected($status === 'active')>Activos</option>
                <option value="inactive" @selected($status === 'inactive')>Inactivos</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="min-h-11 flex-1 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Aplicar</button>
            @if ($search !== '' || $status)
                <a href="{{ route('patients.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Limpiar</a>
            @endif
        </div>
    </form>

    <div class="mt-6">
        @if ($patients->isEmpty())
            <x-card>
                <x-empty-state
                    :title="$search !== '' || $status ? 'No encontramos coincidencias' : 'Todavía no hay pacientes'"
                    :description="$search !== '' || $status ? 'Prueba otro término o limpia los filtros para ver todos los pacientes.' : 'Registra al primer paciente para comenzar a gestionar sus datos.'"
                >
                    <x-slot:action>
                        @if ($search !== '' || $status)
                            <a href="{{ route('patients.index') }}" class="inline-flex min-h-11 items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Limpiar filtros</a>
                        @else
                            <a href="{{ route('patients.create') }}" class="inline-flex min-h-11 items-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Registrar primer paciente</a>
                        @endif
                    </x-slot:action>
                </x-empty-state>
            </x-card>
        @else
            <div class="grid gap-4 md:hidden">
                @foreach ($patients as $patient)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0"><h2 class="font-semibold text-slate-950">{{ $patient->full_name }}</h2><p class="mt-1 text-sm text-slate-600">{{ $patient->whatsapp_phone }}</p></div>
                            <span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $patient->status === 'active', 'bg-slate-200 text-slate-700' => $patient->status === 'inactive'])>{{ $patient->status === 'active' ? 'Activo' : 'Inactivo' }}</span>
                        </div>
                        <p class="mt-4 text-sm text-slate-600">DNI: {{ $patient->dni ?: 'No registrado' }}</p>
                        <a href="{{ route('patients.show', $patient) }}" class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-brand-200 bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-800">Ver detalle</a>
                    </article>
                @endforeach
            </div>

            <x-table-container class="hidden md:block">
                <table class="w-full text-left">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Paciente</th><th class="px-5 py-3">Teléfono</th><th class="px-5 py-3">DNI</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3 text-right">Acción</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($patients as $patient)
                            <tr><td class="px-5 py-4 font-semibold text-slate-950">{{ $patient->full_name }}</td><td class="px-5 py-4 text-sm text-slate-700">{{ $patient->whatsapp_phone }}</td><td class="px-5 py-4 text-sm text-slate-700">{{ $patient->dni ?: 'No registrado' }}</td><td class="px-5 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $patient->status === 'active', 'bg-slate-200 text-slate-700' => $patient->status === 'inactive'])>{{ $patient->status === 'active' ? 'Activo' : 'Inactivo' }}</span></td><td class="px-5 py-4 text-right"><a href="{{ route('patients.show', $patient) }}" class="font-semibold text-brand-700 hover:text-brand-900">Ver detalle</a></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-container>

            <div class="mt-5">{{ $patients->links() }}</div>
        @endif
    </div>
</x-layouts.authenticated>
