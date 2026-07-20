@props(['title', 'description' => null])

<header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        <p class="text-sm font-semibold text-brand-700">Área de trabajo</p>
        <h1 class="mt-1 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">{{ $description }}</p>
        @endif
    </div>

    @if (isset($actions))
        <div class="shrink-0">{{ $actions }}</div>
    @endif
</header>
