@php
    $editing = isset($routineTemplate);
    $stored = $editing ? $routineTemplate->exercises->map(fn($item) => [
        'id' => $item->id, 'source_exercise_id' => $item->source_exercise_id, 'position' => $item->position,
        'name' => $item->name, 'description' => $item->description, 'duration_seconds' => $item->duration_seconds,
        'sets' => $item->sets, 'repetitions' => $item->repetitions, 'material_url' => $item->material_url,
    ])->values()->all() : [];
    $initialExercises = old('exercises', $stored);
@endphp

@if($errors->any())<x-alert type="error" class="mb-6">No pudimos guardar la plantilla. Revisa los campos marcados y vuelve a intentarlo.</x-alert>@endif

<form method="POST" action="{{ $editing ? route('routine-templates.update', $routineTemplate) : route('routine-templates.store') }}" x-data="routineTemplateEditor(@js(route('routine-templates.exercise-search')), @js($initialExercises))" x-init="init" x-on:submit="prepareSubmit" class="space-y-8">
    @csrf
    @if($editing) @method('PUT') <input type="hidden" name="updated_at" value="{{ old('updated_at', $routineTemplate->updated_at->toISOString()) }}"> @endif

    <x-card>
        <div class="border-b border-slate-200 px-5 py-5 sm:px-6"><h2 class="text-lg font-semibold">Datos de la plantilla</h2><p class="mt-1 text-sm text-slate-600">El nombre es obligatorio. Una plantilla vacía puede guardarse, pero no podrá usarse en un plan hasta tener ejercicios.</p></div>
        <div class="p-5 sm:p-6"><label for="name" class="block text-sm font-semibold">Nombre <span class="text-red-700">*</span></label><input id="name" name="name" required maxlength="160" value="{{ old('name', $routineTemplate->name ?? '') }}" @class(['mt-2 min-h-11 w-full rounded-xl border px-3 py-2', 'border-red-400' => $errors->has('name'), 'border-slate-300' => !$errors->has('name')]) aria-describedby="name_error">@error('name')<p id="name_error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror</div>
    </x-card>

    <x-card>
        <div class="border-b border-slate-200 px-5 py-5 sm:px-6"><h2 class="text-lg font-semibold">Agregar desde ejercicios</h2><p class="mt-1 text-sm text-slate-600">La búsqueda muestra hasta 10 ejercicios activos. Al agregar uno se crea una copia que podrás modificar sin alterar su origen.</p></div>
        <div class="p-5 sm:p-6">
            <div class="flex flex-col gap-2 sm:flex-row"><label for="exercise-search" class="sr-only">Buscar ejercicio</label><input id="exercise-search" type="search" x-model="query" x-on:input.debounce.300ms="search" placeholder="Buscar por nombre o descripción" class="min-h-11 flex-1 rounded-xl border border-slate-300 px-3 py-2"><button type="button" x-on:click="search" class="min-h-11 rounded-xl bg-slate-900 px-5 text-sm font-semibold text-white">Buscar</button></div>
            <p x-show="loading" class="mt-4 text-sm text-slate-600" role="status">Buscando ejercicios…</p>
            <p x-show="!loading && results.length === 0" class="mt-4 text-sm text-slate-600">No hay ejercicios activos que coincidan.</p>
            <ul x-show="!loading && results.length" class="mt-4 grid gap-3 md:grid-cols-2">
                <template x-for="result in results" :key="result.id"><li class="flex min-w-0 items-center justify-between gap-3 rounded-xl border border-slate-200 p-3"><div class="min-w-0"><p class="truncate font-semibold" x-text="result.name"></p><p class="truncate text-sm text-slate-500" x-text="result.description || 'Sin descripción'"></p></div><button type="button" x-on:click="add(result)" class="min-h-11 shrink-0 rounded-xl border border-brand-200 bg-brand-50 px-3 text-sm font-semibold text-brand-800">Agregar</button></li></template>
            </ul>
        </div>
    </x-card>

    <x-card>
        <div class="border-b border-slate-200 px-5 py-5 sm:px-6"><h2 class="text-lg font-semibold">Composición y orden</h2><p class="mt-1 text-sm text-slate-600">Usa Subir y Bajar para mantener una secuencia accesible. Puedes repetir un ejercicio.</p></div>
        @error('exercises')<p class="mx-5 mt-5 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $message }}</p>@enderror
        <div x-show="items.length === 0" class="p-8 text-center text-sm text-slate-600">Esta plantilla todavía no tiene ejercicios.</div>
        <div class="divide-y divide-slate-200">
            <template x-for="(item, index) in items" :key="item.key">
                <fieldset class="min-w-0 p-5 sm:p-6"><legend class="sr-only" x-text="`Ejercicio ${index + 1}`"></legend>
                    <input type="hidden" :name="`exercises[${index}][id]`" :value="item.id || ''"><input type="hidden" :name="`exercises[${index}][source_exercise_id]`" :value="item.source_exercise_id || ''"><input type="hidden" :name="`exercises[${index}][position]`" :value="index + 1">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><h3 class="min-w-0 break-words font-semibold"><span class="text-brand-700" x-text="`${index + 1}.`"></span> <span x-text="item.name"></span></h3><div class="flex flex-wrap gap-2"><button type="button" x-on:click="move(index,-1)" :disabled="index===0" class="min-h-11 rounded-xl border border-slate-300 px-3 text-sm font-semibold disabled:opacity-40">Subir</button><button type="button" x-on:click="move(index,1)" :disabled="index===items.length-1" class="min-h-11 rounded-xl border border-slate-300 px-3 text-sm font-semibold disabled:opacity-40">Bajar</button><button type="button" x-on:click="remove(index)" class="min-h-11 rounded-xl border border-red-200 px-3 text-sm font-semibold text-red-700">Retirar</button></div></div>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2"><label :for="`copy-name-${item.key}`" class="block text-sm font-semibold">Nombre copiado</label><input :id="`copy-name-${item.key}`" :name="`exercises[${index}][name]`" x-model="item.name" required maxlength="160" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 px-3 py-2"></div>
                        <div class="sm:col-span-2"><label :for="`copy-description-${item.key}`" class="block text-sm font-semibold">Descripción <span class="font-normal text-slate-500">(opcional)</span></label><textarea :id="`copy-description-${item.key}`" :name="`exercises[${index}][description]`" x-model="item.description" rows="3" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2"></textarea></div>
                        <div><label :for="`copy-duration-${item.key}`" class="block text-sm font-semibold">Duración en segundos</label><input :id="`copy-duration-${item.key}`" :name="`exercises[${index}][duration_seconds]`" x-model="item.duration_seconds" type="number" min="1" max="2147483647" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 px-3 py-2"></div>
                        <div><label :for="`copy-sets-${item.key}`" class="block text-sm font-semibold">Sets</label><input :id="`copy-sets-${item.key}`" :name="`exercises[${index}][sets]`" x-model="item.sets" type="number" min="1" max="32767" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 px-3 py-2"></div>
                        <div><label :for="`copy-repetitions-${item.key}`" class="block text-sm font-semibold">Repeticiones</label><input :id="`copy-repetitions-${item.key}`" :name="`exercises[${index}][repetitions]`" x-model="item.repetitions" type="number" min="1" max="32767" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 px-3 py-2"></div>
                        <div class="sm:col-span-2"><label :for="`copy-url-${item.key}`" class="block text-sm font-semibold">URL del material</label><input :id="`copy-url-${item.key}`" :name="`exercises[${index}][material_url]`" x-model="item.material_url" type="url" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 px-3 py-2"></div>
                    </div>
                </fieldset>
            </template>
        </div>
    </x-card>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><a href="{{ $editing ? route('routine-templates.show', $routineTemplate) : route('routine-templates.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 text-sm font-semibold">Cancelar</a><button type="submit" x-bind:disabled="submitting" class="min-h-11 rounded-xl bg-brand-600 px-5 text-sm font-semibold text-white disabled:opacity-60"><span x-show="!submitting">Guardar plantilla</span><span x-cloak x-show="submitting">Guardando…</span></button></div>
</form>
