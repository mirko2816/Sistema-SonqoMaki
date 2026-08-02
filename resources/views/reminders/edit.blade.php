<x-layouts.authenticated :title="'Recordatorios · '.$plan->name">
    <x-page-header :title="'Recordatorios de '.$plan->name" :description="$plan->patient->full_name">
        <x-slot:actions>
            <a href="{{ route('plans.show', $plan) }}" class="inline-flex min-h-11 items-center rounded-xl border border-slate-300 px-4 text-sm font-semibold">Volver al plan</a>
        </x-slot:actions>
    </x-page-header>

    @if($errors->any())
        <x-alert type="error" class="mt-6">
            <p class="font-semibold">Revisa la programación:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </x-alert>
    @endif

    <x-alert class="mt-6">
        Los horarios se interpretan siempre en <strong>America/Lima</strong>. Puedes guardarlos aunque el plan esté en pausa o incompleto.
    </x-alert>

    @php
        $days = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
        $stored = $plan->reminderConfiguration->schedules->groupBy('weekday')->map(fn($items) => $items->pluck('send_at')->map(fn($time) => substr($time, 0, 5))->values()->all())->all();
        $submitted = old('schedules', $stored);
    @endphp

    <form method="POST" action="{{ route('reminders.update', $plan) }}" class="mt-8 space-y-6">
        @csrf
        @method('PUT')
        <x-card class="p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Estado general</h2>
                    <p class="mt-1 text-sm text-slate-600">Pausar conserva todos los horarios y no pausa el plan.</p>
                </div>
                <label class="flex min-h-11 items-center gap-3 rounded-xl border border-slate-300 px-4 py-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="size-5 rounded border-slate-300 text-brand-600" @checked(old('is_active', $plan->reminderConfiguration->is_active))>
                    <span class="font-semibold">Recordatorios activos</span>
                </label>
            </div>
        </x-card>

        <div class="grid gap-4 lg:grid-cols-2">
            @foreach($days as $weekday => $day)
                @php($times = is_array($submitted[$weekday] ?? null) ? $submitted[$weekday] : [])
                <fieldset class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <legend class="px-1 text-base font-semibold">{{ $day }}</legend>
                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                        @foreach([0, 1] as $slot)
                            <label class="block">
                                <span class="text-sm font-medium text-slate-700">Horario {{ $slot + 1 }}</span>
                                <input type="time" name="schedules[{{ $weekday }}][]" value="{{ $times[$slot] ?? '' }}" aria-label="{{ $day }}, horario {{ $slot + 1 }}" class="mt-1 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-base focus:border-brand-500 focus:outline-none">
                            </label>
                        @endforeach
                    </div>
                    @error("schedules.$weekday")<p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>@enderror
                </fieldset>
            @endforeach
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('reminders.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl px-5 text-sm font-semibold text-slate-700">Cancelar</a>
            <button type="submit" class="min-h-11 rounded-xl bg-brand-600 px-6 text-sm font-semibold text-white hover:bg-brand-700">Guardar programación</button>
        </div>
    </form>
</x-layouts.authenticated>
