<x-layouts.authenticated title="Ejercicios">
    <x-page-header title="Biblioteca de ejercicios" description="Busca, consulta y administra ejercicios reutilizables para futuras rutinas.">
        <x-slot:actions>
            <a href="{{ route('exercises.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Nuevo ejercicio</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('exercises.index') }}" class="mt-8 grid gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end sm:p-5">
        <div>
            <label for="search" class="block text-sm font-semibold text-slate-800">Buscar ejercicio</label>
            <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Nombre o descripción" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-950 shadow-sm">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="min-h-11 flex-1 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Buscar</button>
            @if ($search !== '')
                <a href="{{ route('exercises.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Limpiar</a>
            @endif
        </div>
    </form>

    <div class="mt-6">
        @if ($exercises->isEmpty())
            <x-card>
                <x-empty-state
                    :title="$search !== '' ? 'No encontramos ejercicios' : 'Todavía no hay ejercicios'"
                    :description="$search !== '' ? 'Prueba otro término o limpia la búsqueda para ver toda la biblioteca.' : 'Crea el primer ejercicio reutilizable de la biblioteca.'"
                >
                    <x-slot:action>
                        @if ($search !== '')
                            <a href="{{ route('exercises.index') }}" class="inline-flex min-h-11 items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Limpiar búsqueda</a>
                        @else
                            <a href="{{ route('exercises.create') }}" class="inline-flex min-h-11 items-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Crear primer ejercicio</a>
                        @endif
                    </x-slot:action>
                </x-empty-state>
            </x-card>
        @else
            <div class="grid gap-4 md:hidden">
                @foreach ($exercises as $exercise)
                    <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="break-words font-semibold text-slate-950">{{ $exercise->name }}</h2>
                        <p class="mt-2 line-clamp-2 break-words text-sm leading-6 text-slate-600">{{ $exercise->description ?: 'Sin descripción configurada.' }}</p>
                        <dl class="mt-4 grid grid-cols-3 gap-2 text-center text-sm">
                            <div class="rounded-xl bg-slate-50 p-2"><dt class="text-xs text-slate-500">Duración</dt><dd class="mt-1 font-semibold text-slate-800">{{ $exercise->formatted_duration }}</dd></div>
                            <div class="rounded-xl bg-slate-50 p-2"><dt class="text-xs text-slate-500">Sets</dt><dd class="mt-1 font-semibold text-slate-800">{{ $exercise->sets ?? '—' }}</dd></div>
                            <div class="rounded-xl bg-slate-50 p-2"><dt class="text-xs text-slate-500">Repet.</dt><dd class="mt-1 font-semibold text-slate-800">{{ $exercise->repetitions ?? '—' }}</dd></div>
                        </dl>
                        <a href="{{ route('exercises.show', $exercise) }}" class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-brand-200 bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-800">Ver detalle</a>
                    </article>
                @endforeach
            </div>

            <x-table-container class="hidden md:block">
                <table class="w-full table-fixed text-left">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="w-2/5 px-5 py-3">Ejercicio</th><th class="px-5 py-3">Duración</th><th class="px-5 py-3">Sets</th><th class="px-5 py-3">Repeticiones</th><th class="px-5 py-3 text-right">Acción</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($exercises as $exercise)
                            <tr>
                                <td class="px-5 py-4"><p class="break-words font-semibold text-slate-950">{{ $exercise->name }}</p><p class="mt-1 truncate text-sm text-slate-600">{{ $exercise->description ?: 'Sin descripción' }}</p></td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $exercise->formatted_duration }}</td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $exercise->sets ?? '—' }}</td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $exercise->repetitions ?? '—' }}</td>
                                <td class="px-5 py-4 text-right"><a href="{{ route('exercises.show', $exercise) }}" class="font-semibold text-brand-700 hover:text-brand-900">Ver detalle</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-container>

            <div class="mt-5">{{ $exercises->links() }}</div>
        @endif
    </div>
</x-layouts.authenticated>
