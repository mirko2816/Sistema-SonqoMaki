@props(['title'])

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0f7265">

        <title>{{ $title }} · {{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900 antialiased">
        <a href="#contenido-principal" class="fixed left-4 top-4 z-[60] -translate-y-24 rounded-lg bg-slate-950 px-4 py-3 text-sm font-semibold text-white focus:translate-y-0">
            Ir al contenido principal
        </a>

        <div class="min-h-screen lg:grid lg:grid-cols-[19rem_minmax(0,1fr)]">
            <aside class="hidden border-r border-slate-200 bg-white lg:fixed lg:inset-y-0 lg:flex lg:w-76 lg:flex-col" aria-label="Barra lateral">
                <div class="border-b border-slate-100 px-5 py-5">
                    <x-brand />
                </div>

                <x-navigation class="flex-1 overflow-y-auto px-4 py-5" />

                <div class="border-t border-slate-100 p-4">
                    <p class="truncate px-2 text-xs text-slate-500">Sesión iniciada como</p>
                    <p class="mt-1 truncate px-2 text-sm font-semibold text-slate-800">{{ auth()->user()->email }}</p>
                    <form class="mt-3" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 focus-visible:outline-none">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </aside>

            <div class="min-w-0 lg:col-start-2">
                <div x-data="mobileNavigation" x-on:keydown.escape.window="close" class="lg:hidden">
                    <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6">
                        <x-brand compact />
                        <button
                            x-ref="menuButton"
                            type="button"
                            class="flex size-11 items-center justify-center rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 focus-visible:outline-none"
                            x-on:click="open"
                            x-bind:aria-expanded="isOpen"
                            aria-controls="mobile-navigation"
                            aria-label="Abrir menú principal"
                        >
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </button>
                    </header>

                    <div x-cloak x-show="isOpen" class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Menú principal">
                        <button type="button" class="absolute inset-0 bg-slate-950/40" x-on:click="close" tabindex="-1" aria-label="Cerrar menú"></button>
                        <div
                            id="mobile-navigation"
                            x-ref="panel"
                            x-on:keydown.tab="trapFocus"
                            class="absolute inset-y-0 left-0 flex w-[min(21rem,88vw)] flex-col bg-white shadow-2xl"
                        >
                            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                                <x-brand />
                                <button
                                    x-ref="closeButton"
                                    type="button"
                                    class="flex size-11 items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100 focus-visible:outline-none"
                                    x-on:click="close"
                                    aria-label="Cerrar menú principal"
                                >
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                                    </svg>
                                </button>
                            </div>

                            <x-navigation mobile class="flex-1 overflow-y-auto px-4 py-5" />

                            <div class="border-t border-slate-100 p-4">
                                <p class="truncate px-2 text-xs text-slate-500">{{ auth()->user()->email }}</p>
                                <form class="mt-3" method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="min-h-11 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 focus-visible:outline-none">
                                        Cerrar sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <main id="contenido-principal" class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-10 lg:py-12" tabindex="-1">
                    @if (session('status'))
                        <x-alert class="mb-6">{{ session('status') }}</x-alert>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
