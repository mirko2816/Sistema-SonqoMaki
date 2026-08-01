<x-layouts.authenticated :title="$exercise->name">
    <x-page-header :title="$exercise->name" description="Detalle del ejercicio reutilizable de la biblioteca.">
        <x-slot:actions>
            <a href="{{ route('exercises.edit', $exercise) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Editar ejercicio</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mt-8 grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <x-card class="min-w-0 p-5 sm:p-6">
            <dl class="grid gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2"><dt class="text-sm font-semibold text-slate-500">Descripción</dt><dd class="mt-2 whitespace-pre-line break-words text-slate-900">{{ $exercise->description ?: 'No configurada' }}</dd></div>
                <div><dt class="text-sm font-semibold text-slate-500">Duración</dt><dd class="mt-2 text-slate-900">{{ $exercise->formatted_duration }}</dd></div>
                <div><dt class="text-sm font-semibold text-slate-500">Sets</dt><dd class="mt-2 text-slate-900">{{ $exercise->sets ?? 'No configurados' }}</dd></div>
                <div><dt class="text-sm font-semibold text-slate-500">Repeticiones</dt><dd class="mt-2 text-slate-900">{{ $exercise->repetitions ?? 'No configuradas' }}</dd></div>
                <div class="min-w-0 sm:col-span-2">
                    <dt class="text-sm font-semibold text-slate-500">Material externo</dt>
                    <dd class="mt-2 min-w-0">
                        @if ($exercise->material_url)
                            <a href="{{ $exercise->material_url }}" target="_blank" rel="noopener noreferrer" class="block break-all font-semibold text-brand-700 underline decoration-brand-200 underline-offset-4 hover:text-brand-900">Abrir material externo<span class="sr-only"> (se abre en otra pestaña)</span></a>
                            <p class="mt-2 break-all text-xs text-slate-500">{{ $exercise->material_url }}</p>
                        @else
                            <span class="text-slate-900">No configurado</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </x-card>

        <aside class="space-y-4">
            <x-card class="border-red-200 p-5">
                <h2 class="font-semibold text-red-900">Retirar ejercicio</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Dejará de estar disponible para nuevas rutinas. Las copias ya incorporadas a rutinas o plantillas no se modificarán.</p>
                <form method="POST" action="{{ route('exercises.destroy', $exercise) }}" class="mt-4" x-data="{ confirmed: false, submitting: false }" x-on:submit="submitting = true">
                    @csrf @method('DELETE')
                    <label class="flex items-start gap-3 text-sm text-slate-700"><input type="checkbox" name="retirement_confirmed" value="1" required x-model="confirmed" class="mt-0.5 size-4 rounded border-slate-400"><span>Confirmo que comprendo el efecto y deseo retirar este ejercicio.</span></label>
                    @error('retirement_confirmed')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    <button type="submit" x-bind:disabled="!confirmed || submitting" class="mt-4 min-h-11 w-full rounded-xl bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-50">Retirar ejercicio</button>
                </form>
            </x-card>
            <a href="{{ route('exercises.index') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Volver a la biblioteca</a>
        </aside>
    </div>
</x-layouts.authenticated>
