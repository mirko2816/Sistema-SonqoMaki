@props(['title'])

@php
    $publicAssets = (clone app(\Illuminate\Foundation\Vite::class))
        ->useHotFile(storage_path('framework/vite-public.hot'));
@endphp

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#6F1A84">
        <meta name="robots" content="noindex, nofollow, noarchive">
        <meta name="referrer" content="no-referrer">

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

        <title>{{ $title }} · {{ config('app.name') }}</title>

        <link rel="stylesheet" href="{{ $publicAssets->asset('resources/css/app.css') }}">
    </head>
    <body class="min-h-screen overflow-x-hidden bg-brand-50 text-ink-900 antialiased">
        <a href="#contenido-principal" class="fixed left-4 top-4 z-50 -translate-y-24 rounded-xl bg-ink-950 px-4 py-3 text-sm font-semibold text-white focus:translate-y-0">
            Ir al contenido
        </a>

        <div class="mx-auto flex min-h-screen w-full max-w-3xl flex-col px-4 py-5 sm:px-7 sm:py-8">
            <header class="flex items-center justify-center py-2 sm:justify-start">
                <x-brand />
            </header>

            <main id="contenido-principal" class="flex-1 py-7 sm:py-10" tabindex="-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-brand-100 py-5 text-center text-xs leading-5 text-ink-500 sm:text-left">
                Sonqo Maki · Tu salud en buenas manos
            </footer>
        </div>
    </body>
</html>
