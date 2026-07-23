@props(['mobile' => false])

@php
    $items = [
        ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'dashboard'],
        ['label' => 'Pacientes', 'icon' => 'users', 'route' => 'patients.index', 'pattern' => 'patients.*'],
        ['label' => 'Ejercicios', 'icon' => 'activity', 'route' => 'exercises.index', 'pattern' => 'exercises.*'],
        ['label' => 'Rutinas', 'icon' => 'clipboard', 'route' => 'routine-templates.index', 'pattern' => 'routine-templates.*'],
        ['label' => 'Planes', 'icon' => 'calendar', 'route' => 'plans.index', 'pattern' => 'plans.*'],
        ['label' => 'Recordatorios', 'icon' => 'bell'],
        ['label' => 'Historial de envíos', 'icon' => 'history'],
    ];
@endphp

<nav aria-label="Navegación principal" {{ $attributes }}>
    <ul class="space-y-1.5">
        @foreach ($items as $item)
            <li>
                @if (isset($item['route']))
                    <a
                        href="{{ route($item['route']) }}"
                        @class([
                            'flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold focus-visible:outline-none',
                            'bg-brand-50 text-brand-800 ring-1 ring-inset ring-brand-200' => request()->routeIs($item['pattern'] ?? $item['route']),
                            'text-slate-700 hover:bg-slate-100 hover:text-slate-950' => ! request()->routeIs($item['pattern'] ?? $item['route']),
                        ])
                        @if (request()->routeIs($item['pattern'] ?? $item['route'])) aria-current="page" @endif
                    >
                        <x-navigation-icon :name="$item['icon']" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @else
                    <span class="flex min-h-11 cursor-not-allowed items-start gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-500" aria-disabled="true">
                        <x-navigation-icon :name="$item['icon']" />
                        <span class="min-w-0 flex-1">
                            <span class="block font-medium leading-5">{{ $item['label'] }}</span>
                            <span class="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500">Próximamente</span>
                        </span>
                    </span>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
