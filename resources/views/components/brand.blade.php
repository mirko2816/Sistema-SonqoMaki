@props(['compact' => false])

<div {{ $attributes->class(['flex min-w-0 items-center gap-3']) }}>
    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-sm font-bold text-white shadow-sm" aria-hidden="true">
        SM
    </span>
    <span class="min-w-0">
        <span class="block truncate font-semibold tracking-tight text-slate-950">Sonqo Maki</span>
        @unless ($compact)
            <span class="block truncate text-xs text-slate-500">Rehabilitación y acompañamiento</span>
        @endunless
    </span>
</div>
