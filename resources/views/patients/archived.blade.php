<x-layouts.authenticated title="Pacientes archivados">
    <x-page-header title="Pacientes archivados" description="Consulta los pacientes retirados de la operación normal. La restauración es una operación técnica fuera de la interfaz del MVP.">
        <x-slot:actions><a href="{{ route('patients.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Volver a pacientes</a></x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('patients.archived') }}" class="mt-8 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end sm:p-5">
        <div class="min-w-0 flex-1">
            <label for="search" class="block text-sm font-semibold text-slate-800">Buscar archivado</label>
            <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Nombre, apellido, DNI o teléfono" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-950 shadow-sm">
        </div>
        <button type="submit" class="min-h-11 rounded-xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-700">Buscar</button>
        @if ($search !== '')<a href="{{ route('patients.archived') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-700">Limpiar</a>@endif
    </form>

    <div class="mt-6">
        @if ($patients->isEmpty())
            <x-card><x-empty-state :title="$search !== '' ? 'No encontramos archivados' : 'No hay pacientes archivados'" :description="$search !== '' ? 'Prueba otro término o limpia la búsqueda.' : 'Los pacientes que archives aparecerán aquí únicamente para consulta.'" /></x-card>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($patients as $patient)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-slate-950">{{ $patient->full_name }}</h2>
                        <dl class="mt-4 space-y-3 text-sm"><div><dt class="text-slate-500">Teléfono</dt><dd class="mt-1 text-slate-900">{{ $patient->whatsapp_phone }}</dd></div><div><dt class="text-slate-500">DNI</dt><dd class="mt-1 text-slate-900">{{ $patient->dni ?: 'No registrado' }}</dd></div><div><dt class="text-slate-500">Archivado</dt><dd class="mt-1 text-slate-900">{{ $patient->deleted_at->translatedFormat('d \d\e F \d\e Y') }}</dd></div></dl>
                    </article>
                @endforeach
            </div>
            <div class="mt-5">{{ $patients->links() }}</div>
        @endif
    </div>
</x-layouts.authenticated>
