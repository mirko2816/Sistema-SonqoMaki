<x-layouts.authenticated title="Rutinas">
    <x-page-header title="Biblioteca de rutinas" description="Crea y administra plantillas reutilizables, independientes de pacientes y fechas.">
        <x-slot:actions><a href="{{ route('routine-templates.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Nueva plantilla</a></x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('routine-templates.index') }}" class="mt-8 grid gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end sm:p-5">
        <div><label for="search" class="block text-sm font-semibold text-slate-800">Buscar plantilla</label><input id="search" name="search" type="search" value="{{ $search }}" placeholder="Nombre de la plantilla" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 px-3 py-2"></div>
        <div class="flex gap-2"><button class="min-h-11 flex-1 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Buscar</button>@if($search !== '')<a href="{{ route('routine-templates.index') }}" class="inline-flex min-h-11 items-center rounded-xl border border-slate-300 px-4 text-sm font-semibold">Limpiar</a>@endif</div>
    </form>

    <div class="mt-6">
        @if($templates->isEmpty())
            <x-card><x-empty-state :title="$search !== '' ? 'No encontramos plantillas' : 'Todavía no hay plantillas'" :description="$search !== '' ? 'Prueba otro término o limpia la búsqueda.' : 'Crea la primera rutina reutilizable de la biblioteca.'"><x-slot:action><a href="{{ $search !== '' ? route('routine-templates.index') : route('routine-templates.create') }}" class="inline-flex min-h-11 items-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white">{{ $search !== '' ? 'Limpiar búsqueda' : 'Crear primera plantilla' }}</a></x-slot:action></x-empty-state></x-card>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($templates as $template)
                    <article class="flex min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="break-words text-lg font-semibold text-slate-950">{{ $template->name }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ $template->exercises_count }} {{ $template->exercises_count === 1 ? 'ejercicio configurado' : 'ejercicios configurados' }}</p>
                        <div class="mt-5 grid grid-cols-2 gap-2"><a href="{{ route('routine-templates.show', $template) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-brand-200 bg-brand-50 px-3 text-sm font-semibold text-brand-800">Consultar</a><a href="{{ route('routine-templates.edit', $template) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-3 text-sm font-semibold text-slate-700">Editar</a></div>
                    </article>
                @endforeach
            </div>
            <div class="mt-5">{{ $templates->links() }}</div>
        @endif
    </div>
</x-layouts.authenticated>
