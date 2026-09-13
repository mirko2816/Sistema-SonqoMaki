@props(['compact' => false])

<div {{ $attributes->class(['flex min-w-0 items-center gap-3']) }}>
    @if ($compact)
        <img src="{{ asset('images/brand/logo-principal.svg') }}" alt="" width="56" height="40" class="h-10 w-14 shrink-0 object-contain">
        <span class="truncate font-semibold tracking-tight text-ink-950">Sonqo Maki</span>
    @else
        <img src="{{ asset('images/brand/logo-simbolo.svg') }}" alt="Sonqo Maki · Tu salud en buenas manos" width="160" height="118" class="h-auto w-40 max-w-full object-contain">
    @endif
</div>
