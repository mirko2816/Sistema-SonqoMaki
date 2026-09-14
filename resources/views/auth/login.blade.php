<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#6F1A84">

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

        <title>Iniciar sesión · {{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-ink-50 text-ink-900 antialiased">
        <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-5 py-10 sm:px-8">
            <div class="absolute inset-0 -z-10 bg-gradient-to-br from-secondary/60 via-brand-50 to-brand-100"></div>
            <div class="absolute -top-24 right-[-7rem] -z-10 h-72 w-72 rounded-full bg-accent/20 blur-3xl"></div>

            <section class="isolate grid w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl shadow-ink-900/10 ring-1 ring-inset ring-ink-200/80 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="hidden bg-brand-600 p-12 text-white lg:flex lg:flex-col lg:justify-center">
                    <div>
                        <div class="mx-auto w-fit rounded-2xl bg-surface p-5">
                            <img src="{{ asset('images/brand/logo-principal.svg') }}" alt="Sonqo Maki" width="192" height="138" class="h-auto w-48">
                        </div>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight tracking-tight">
                            Tu salud en buenas manos
                        </h1>
                        <p class="mt-5 max-w-sm leading-7 text-brand-100">
                            Acceso privado para especialistas.
                        </p>
                    </div>
                </div>

                <div class="p-7 sm:p-12 lg:p-14">
                    <div class="lg:hidden">
                        <img src="{{ asset('images/brand/logo-principal.svg') }}" alt="Sonqo Maki" width="176" height="126" class="mx-auto h-auto w-44">
                        <p class="mt-4 text-center text-sm text-brand-700">Tu salud en buenas manos</p>
                    </div>

                    <div class="mt-8 lg:mt-0">
                        <p class="text-sm font-semibold text-brand-700">Acceso para especialistas</p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-ink-950">Inicia sesión</h2>
                        <p class="mt-3 text-sm leading-6 text-ink-600">Ingresa con la cuenta proporcionada para acceder al área privada.</p>
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
                            <label for="email" class="block text-sm font-semibold text-ink-800">Correo electrónico</label>
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
                                    'mt-2 block w-full rounded-xl border bg-white px-4 py-3 text-ink-950 shadow-sm outline-none transition placeholder:text-ink-400 focus:ring-4',
                                    'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('email'),
                                    'border-ink-300 focus:border-brand-600 focus:ring-brand-100' => ! $errors->has('email'),
                                ])
                                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                            >
                            @error('email')
                                <p id="email-error" class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-ink-800">Contraseña</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                required
                                @class([
                                    'mt-2 block w-full rounded-xl border bg-white px-4 py-3 text-ink-950 shadow-sm outline-none transition focus:ring-4',
                                    'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('password'),
                                    'border-ink-300 focus:border-brand-600 focus:ring-brand-100' => ! $errors->has('password'),
                                ])
                                @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                            >
                            @error('password')
                                <p id="password-error" class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="flex w-full items-center justify-center rounded-xl bg-brand-700 px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800 focus:outline-none focus:ring-4 focus:ring-brand-200 disabled:cursor-wait disabled:opacity-70"
                            x-bind:disabled="submitting"
                        >
                            <svg x-cloak x-show="submitting" class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span x-text="submitting ? 'Ingresando…' : 'Iniciar sesión'">Iniciar sesión</span>
                        </button>
                    </form>

                    <p class="mt-8 text-center text-xs leading-5 text-ink-500">
                        El acceso está reservado a cuentas creadas por el responsable técnico.
                    </p>
                </div>
            </section>
        </main>
    </body>
</html>
