@props(['type' => 'success'])

@php
    $styles = match ($type) {
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'error' => 'border-red-200 bg-red-50 text-red-900',
        default => 'border-emerald-200 bg-emerald-50 text-emerald-900',
    };
@endphp

<div {{ $attributes->class("rounded-xl border px-4 py-3 text-sm font-medium $styles") }} role="status">
    {{ $slot }}
</div>
