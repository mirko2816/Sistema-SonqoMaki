@props(['title', 'description'])

<div {{ $attributes->class(['px-5 py-12 text-center sm:px-8 sm:py-16']) }}>
    <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100" aria-hidden="true">
        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v13.5M2 20h20M8 8h8m-8 4h8m-8 4h5" />
        </svg>
    </span>
    <h2 class="mt-5 text-lg font-semibold text-slate-950">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">{{ $description }}</p>
    @if (isset($action))
        <div class="mt-6">{{ $action }}</div>
    @endif
</div>
