<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Iniciar sesión · {{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-5 py-10 sm:px-8">
            <div class="absolute inset-x-0 top-0 -z-10 h-80 bg-gradient-to-br from-teal-100 via-emerald-50 to-sky-100"></div>
            <div class="absolute -top-24 right-[-7rem] -z-10 h-72 w-72 rounded-full bg-teal-200/50 blur-3xl"></div>

            <section class="grid w-full max-w-5xl overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-2xl shadow-slate-900/10 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="hidden bg-slate-900 p-12 text-white lg:flex lg:flex-col lg:justify-between">
                    <div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-400 text-xl font-bold text-slate-950" aria-hidden="true">
                            SM
                        </div>
                        <p class="mt-8 text-sm font-semibold uppercase tracking-[0.22em] text-teal-300">Sonqo Maki</p>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight tracking-tight">
                            Rehabilitación organizada, atención más cercana.
                        </h1>
                        <p class="mt-5 max-w-sm leading-7 text-slate-300">
                            Acceso privado para especialistas. Tus herramientas de trabajo estarán protegidas en una sesión segura.
                        </p>
                    </div>

                    <p class="mt-12 text-sm text-slate-400">Sistema de salud y rehabilitación</p>
                </div>

                <div class="p-7 sm:p-12 lg:p-14">
                    <div class="lg:hidden">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-500 font-bold text-white" aria-hidden="true">
                            SM
                        </div>
                        <p class="mt-4 text-sm font-semibold uppercase tracking-[0.2em] text-teal-700">Sonqo Maki</p>
                    </div>

                    <div class="mt-8 lg:mt-0">
                        <p class="text-sm font-semibold text-teal-700">Acceso para especialistas</p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Inicia sesión</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Ingresa con la cuenta proporcionada para acceder al área privada.</p>
                    </div>

                    @if (session('status'))
                        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form
                        class="mt-8 space-y-6"
                        method="POST"
                        action="{{ route('login.store') }}"
                        x-data="{ submitting: false }"
                        x-on:submit="submitting = true"
                    >
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-800">Correo electrónico</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                inputmode="email"
                                autocomplete="username"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                @class([
                                    'mt-2 block w-full rounded-xl border bg-white px-4 py-3 text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4',
                                    'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('email'),
                                    'border-slate-300 focus:border-teal-600 focus:ring-teal-100' => ! $errors->has('email'),
                                ])
                                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                            >
                            @error('email')
                                <p id="email-error" class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-800">Contraseña</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                required
                                @class([
                                    'mt-2 block w-full rounded-xl border bg-white px-4 py-3 text-slate-950 shadow-sm outline-none transition focus:ring-4',
                                    'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('password'),
                                    'border-slate-300 focus:border-teal-600 focus:ring-teal-100' => ! $errors->has('password'),
                                ])
                                @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                            >
                            @error('password')
                                <p id="password-error" class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="flex w-full items-center justify-center rounded-xl bg-teal-700 px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-200 disabled:cursor-wait disabled:opacity-70"
                            x-bind:disabled="submitting"
                        >
                            <svg x-cloak x-show="submitting" class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span x-text="submitting ? 'Ingresando…' : 'Iniciar sesión'">Iniciar sesión</span>
                        </button>
                    </form>

                    <p class="mt-8 text-center text-xs leading-5 text-slate-500">
                        El acceso está reservado a cuentas creadas por el responsable técnico.
                    </p>
                </div>
            </section>
        </main>
    </body>
</html>
