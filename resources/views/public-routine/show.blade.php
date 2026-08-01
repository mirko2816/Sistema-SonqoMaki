@php
    $pageTitle = match ($result->state) {
        'available' => $result->routine->name,
        'paused' => 'Plan en pausa',
        'finished' => 'Plan finalizado',
        default => 'Contenido no disponible',
    };
@endphp

<x-layouts.public :title="$pageTitle">
    @if ($result->state === 'available')
        <section aria-labelledby="routine-title">
            <div class="rounded-3xl bg-brand-800 px-5 py-7 text-white shadow-lg shadow-brand-900/10 sm:px-8 sm:py-9">
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-brand-100">Tu rutina de hoy</p>
                <h1 id="routine-title" class="mt-3 break-words text-3xl font-bold tracking-tight sm:text-4xl">
                    {{ $result->routine->name }}
                </h1>
                <p class="mt-3 max-w-xl text-base leading-7 text-brand-50">
                    Sigue los ejercicios en el orden indicado y realiza cada movimiento con calma.
                </p>
            </div>

            <ol class="mt-7 space-y-5" aria-label="Ejercicios de la rutina">
                @foreach ($result->routine->exercises as $exercise)
                    <li>
                        <article class="overflow-hidden rounded-3xl border border-brand-100 bg-white shadow-sm">
                            <div class="flex gap-4 p-5 sm:p-7">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-base font-bold text-brand-800" aria-label="Ejercicio {{ $loop->iteration }}">
                                    {{ $loop->iteration }}
                                </span>

                                <div class="min-w-0 flex-1">
                                    <h2 class="break-words text-xl font-bold tracking-tight text-slate-950">{{ $exercise->name }}</h2>

                                    @if ($exercise->description)
                                        <p class="mt-3 whitespace-pre-line break-words text-[0.95rem] leading-7 text-slate-600">{{ $exercise->description }}</p>
                                    @endif

                                    @if ($exercise->sets || $exercise->repetitions || $exercise->duration_seconds)
                                        <dl class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                            @if ($exercise->sets)
                                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Series</dt>
                                                    <dd class="mt-1 text-lg font-bold text-slate-900">{{ $exercise->sets }}</dd>
                                                </div>
                                            @endif
                                            @if ($exercise->repetitions)
                                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Repeticiones</dt>
                                                    <dd class="mt-1 text-lg font-bold text-slate-900">{{ $exercise->repetitions }}</dd>
                                                </div>
                                            @endif
                                            @if ($exercise->duration_seconds)
                                                <div class="col-span-2 rounded-2xl bg-slate-50 px-4 py-3 sm:col-span-1">
                                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Duración</dt>
                                                    <dd class="mt-1 text-lg font-bold text-slate-900">{{ $exercise->formatted_duration }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                    @endif

                                    @if ($exercise->public_material_url)
                                        <a
                                            href="{{ $exercise->public_material_url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            referrerpolicy="no-referrer"
                                            class="mt-5 inline-flex min-h-12 max-w-full items-center justify-center gap-2 rounded-2xl bg-brand-600 px-5 py-3 text-center text-sm font-bold text-white hover:bg-brand-700 focus-visible:outline-none"
                                        >
                                            Abrir material de apoyo
                                            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5h5v5M19 5l-9 9M19 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5" />
                                            </svg>
                                        </a>
                                    @else
                                        <p class="mt-5 text-sm text-slate-500">Este ejercicio no tiene material externo.</p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ol>
        </section>
    @else
        @php
            [$eyebrow, $heading, $message] = match ($result->state) {
                'paused' => [
                    'Plan en pausa',
                    'Tu plan está temporalmente pausado',
                    'Su plan de ejercicios se encuentra pausado. Comuníquese con el especialista encargado.',
                ],
                'finished' => [
                    'Plan completado',
                    'Concluiste tu plan de ejercicios',
                    'Plan de ejercicios finalizado. Para más consultas, comuníquese con el especialista encargado.',
                ],
                default => [
                    'Contenido no disponible',
                    'No podemos mostrar una rutina en este momento',
                    'Revisa que estés usando el enlace correcto. Si necesitas ayuda, comunícate con tu especialista.',
                ],
            };
        @endphp

        <section class="mx-auto max-w-xl rounded-3xl border border-brand-100 bg-white p-6 text-center shadow-sm sm:p-10" aria-labelledby="state-title">
            <span class="mx-auto flex size-16 items-center justify-center rounded-3xl bg-brand-100 text-brand-800" aria-hidden="true">
                @if ($result->state === 'finished')
                    <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>
                @else
                    <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M4.9 19h14.2a2 2 0 0 0 1.73-3L13.73 4a2 2 0 0 0-3.46 0L3.17 16A2 2 0 0 0 4.9 19Z" />
                    </svg>
                @endif
            </span>
            <p class="mt-6 text-sm font-semibold uppercase tracking-[0.14em] text-brand-700">{{ $eyebrow }}</p>
            <h1 id="state-title" class="mt-3 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $heading }}</h1>
            <p class="mt-4 text-base leading-7 text-slate-600">{{ $message }}</p>
        </section>
    @endif
</x-layouts.public>
