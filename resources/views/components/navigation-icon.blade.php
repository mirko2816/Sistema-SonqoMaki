@props(['name'])

<svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
    @switch($name)
        @case('home')
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 11 9-8 9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9Z" />
            @break
        @case('users')
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87m-2-11.96a4 4 0 0 1 0 7.75" />
            @break
        @case('activity')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l2.5-7 5 14 2.5-7h4" />
            @break
        @case('clipboard')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-6 4h6m-6 4h4m-7 8h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-3.2A2 2 0 0 0 13 2h-2a2 2 0 0 0-1.8 1H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z" />
            @break
        @case('calendar')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 2v3m12-3v3M3 9h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
            @break
        @case('bell')
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 13h4" />
            @break
        @case('history')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 3-6.7L3 8m0-5v5h5m4-2v6l4 2" />
            @break
    @endswitch
</svg>
