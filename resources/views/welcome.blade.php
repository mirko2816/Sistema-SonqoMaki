<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
        <main class="mx-auto flex min-h-screen max-w-5xl items-center px-6 py-16 lg:px-8">
            <section class="w-full overflow-hidden rounded-3xl border border-white/10 bg-slate-900 shadow-2xl shadow-black/30">
                <div class="grid gap-10 p-8 sm:p-12 lg:grid-cols-[1.35fr_0.65fr] lg:p-16">
                    <div>
                        <p class="mb-5 inline-flex rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-sm font-medium text-emerald-300">
                            Sonqo Maki · MVP
                        </p>

                        <h1 class="max-w-2xl text-4xl font-semibold tracking-tight text-white sm:text-5xl">
                            Base técnica lista
                        </h1>

                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                            Laravel, Blade, Tailwind CSS y Alpine.js están conectados. Esta es una página de verificación técnica; todavía no es el dashboard del producto.
                        </p>

                        <div class="mt-8" x-data="{ visible: false }">
                            <button
                                type="button"
                                class="rounded-xl bg-emerald-400 px-5 py-3 font-semibold text-slate-950 transition hover:bg-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:ring-offset-2 focus:ring-offset-slate-900"
                                x-on:click="visible = ! visible"
                                x-bind:aria-expanded="visible"
                            >
                                Comprobar Alpine.js
                            </button>

                            <p class="mt-4 text-sm font-medium text-emerald-300" x-cloak x-show="visible" x-transition>
                                Interactividad habilitada correctamente.
                            </p>
                        </div>
                    </div>

                    <dl class="grid content-start gap-4 rounded-2xl border border-white/10 bg-slate-950/60 p-6 text-sm">
                        <div>
                            <dt class="text-slate-400">Laravel</dt>
                            <dd class="mt-1 font-semibold text-white">{{ app()->version() }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">PHP</dt>
                            <dd class="mt-1 font-semibold text-white">{{ PHP_VERSION }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Base de datos</dt>
                            <dd class="mt-1 font-semibold text-white">PostgreSQL</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Zona horaria</dt>
                            <dd class="mt-1 font-semibold text-white">{{ config('app.timezone') }}</dd>
                        </div>
                    </dl>
                </div>
            </section>
        </main>
    </body>
</html>
