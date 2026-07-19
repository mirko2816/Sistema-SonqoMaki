<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Área privada · {{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4 sm:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-600 text-sm font-bold text-white" aria-hidden="true">SM</div>
                    <div>
                        <p class="font-semibold text-slate-950">Sonqo Maki</p>
                        <p class="text-xs text-slate-500">Área privada</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-teal-100">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </header>

        <main class="mx-auto flex max-w-3xl items-center px-5 py-20 sm:px-8">
            <section class="w-full rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-xl shadow-slate-900/5 sm:p-12">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700" aria-hidden="true">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>
                </div>
                <p class="mt-6 text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Sesión activa</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Acceso privado verificado</h1>
                <p class="mx-auto mt-4 max-w-xl leading-7 text-slate-600">
                    La autenticación funciona correctamente. Esta página es provisional y no contiene todavía el dashboard ni funciones del producto.
                </p>
                <p class="mt-6 text-sm text-slate-500">Cuenta: <span class="font-medium text-slate-700">{{ auth()->user()->email }}</span></p>
            </section>
        </main>
    </body>
</html>
