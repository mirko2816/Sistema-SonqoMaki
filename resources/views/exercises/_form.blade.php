@php($editing = isset($exercise))

@if ($errors->has('updated_at'))
    <x-alert type="error" class="mb-6">{{ $errors->first('updated_at') }}</x-alert>
@endif

<form method="POST" action="{{ $editing ? route('exercises.update', $exercise) : route('exercises.store') }}" x-data="{ submitting: false }" x-on:submit="submitting = true" class="space-y-8">
    @csrf
    @if ($editing)
        @method('PUT')
        <input type="hidden" name="updated_at" value="{{ old('updated_at', $exercise->updated_at->toISOString()) }}">
    @endif

    <x-card>
        <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
            <h2 class="text-lg font-semibold text-slate-950">Datos del ejercicio</h2>
            <p class="mt-1 text-sm text-slate-600"><span class="text-red-700">*</span> Campo obligatorio. Los demás campos son opcionales.</p>
        </div>

        <div class="grid gap-6 p-5 sm:grid-cols-2 sm:p-6">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-semibold text-slate-800">Nombre <span class="text-red-700" aria-hidden="true">*</span></label>
                <input id="name" name="name" type="text" maxlength="160" required autocomplete="off" value="{{ old('name', $exercise->name ?? '') }}" @class(['mt-2 min-h-11 w-full rounded-xl border bg-white px-3 py-2 text-slate-950 shadow-sm', 'border-red-400' => $errors->has('name'), 'border-slate-300' => ! $errors->has('name')]) aria-describedby="name_error">
                @error('name')<p id="name_error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-semibold text-slate-800">Descripción <span class="font-normal text-slate-500">(opcional)</span></label>
                <textarea id="description" name="description" rows="4" @class(['mt-2 w-full rounded-xl border bg-white px-3 py-2 text-slate-950 shadow-sm', 'border-red-400' => $errors->has('description'), 'border-slate-300' => ! $errors->has('description')]) aria-describedby="description_help description_error">{{ old('description', $exercise->description ?? '') }}</textarea>
                <p id="description_help" class="mt-2 text-sm text-slate-500">Indicaciones base que luego podrán copiarse y ajustarse en una rutina.</p>
                @error('description')<p id="description_error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="duration_seconds" class="block text-sm font-semibold text-slate-800">Duración en segundos <span class="font-normal text-slate-500">(opcional)</span></label>
                <input id="duration_seconds" name="duration_seconds" type="number" min="1" max="2147483647" step="1" inputmode="numeric" value="{{ old('duration_seconds', $exercise->duration_seconds ?? '') }}" @class(['mt-2 min-h-11 w-full rounded-xl border bg-white px-3 py-2 text-slate-950 shadow-sm', 'border-red-400' => $errors->has('duration_seconds'), 'border-slate-300' => ! $errors->has('duration_seconds')]) aria-describedby="duration_help duration_error">
                <p id="duration_help" class="mt-2 text-sm text-slate-500">Ejemplo: 90 equivale a 1 min 30 s.</p>
                @error('duration_seconds')<p id="duration_error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="sets" class="block text-sm font-semibold text-slate-800">Sets <span class="font-normal text-slate-500">(opcional)</span></label>
                <input id="sets" name="sets" type="number" min="1" max="32767" step="1" inputmode="numeric" value="{{ old('sets', $exercise->sets ?? '') }}" @class(['mt-2 min-h-11 w-full rounded-xl border bg-white px-3 py-2 text-slate-950 shadow-sm', 'border-red-400' => $errors->has('sets'), 'border-slate-300' => ! $errors->has('sets')]) aria-describedby="sets_help sets_error">
                <p id="sets_help" class="mt-2 text-sm text-slate-500">Número entero mayor que cero.</p>
                @error('sets')<p id="sets_error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="repetitions" class="block text-sm font-semibold text-slate-800">Repeticiones <span class="font-normal text-slate-500">(opcional)</span></label>
                <input id="repetitions" name="repetitions" type="number" min="1" max="32767" step="1" inputmode="numeric" value="{{ old('repetitions', $exercise->repetitions ?? '') }}" @class(['mt-2 min-h-11 w-full rounded-xl border bg-white px-3 py-2 text-slate-950 shadow-sm', 'border-red-400' => $errors->has('repetitions'), 'border-slate-300' => ! $errors->has('repetitions')]) aria-describedby="repetitions_help repetitions_error">
                <p id="repetitions_help" class="mt-2 text-sm text-slate-500">Número entero mayor que cero.</p>
                @error('repetitions')<p id="repetitions_error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="material_url" class="block text-sm font-semibold text-slate-800">URL de material externo <span class="font-normal text-slate-500">(opcional)</span></label>
                <input id="material_url" name="material_url" type="url" inputmode="url" autocomplete="url" placeholder="https://ejemplo.com/recurso" value="{{ old('material_url', $exercise->material_url ?? '') }}" @class(['mt-2 min-h-11 w-full rounded-xl border bg-white px-3 py-2 text-slate-950 shadow-sm', 'border-red-400' => $errors->has('material_url'), 'border-slate-300' => ! $errors->has('material_url')]) aria-describedby="url_help url_error">
                <p id="url_help" class="mt-2 text-sm text-slate-500">Admite cualquier recurso con http o https. Sonqo Maki no descarga ni almacena su contenido.</p>
                @error('material_url')<p id="url_error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </x-card>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $editing ? route('exercises.show', $exercise) : route('exercises.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancelar</a>
        <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 disabled:cursor-wait disabled:opacity-60">
            <span x-show="!submitting">{{ $editing ? 'Guardar cambios' : 'Registrar ejercicio' }}</span>
            <span x-cloak x-show="submitting">Guardando…</span>
        </button>
    </div>
</form>
