@php($editing = isset($patient))

@if ($errors->has('updated_at'))
    <x-alert type="error" class="mb-6">{{ $errors->first('updated_at') }}</x-alert>
@endif

<form method="POST" action="{{ $editing ? route('patients.update', $patient) : route('patients.store') }}" x-data="{ submitting: false }" x-on:submit="submitting = true" class="space-y-8">
    @csrf
    @if ($editing)
        @method('PUT')
        <input type="hidden" name="updated_at" value="{{ old('updated_at', $patient->updated_at->toISOString()) }}">
    @endif

    <x-card>
        <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
            <h2 class="text-lg font-semibold text-slate-950">Datos del paciente</h2>
            <p class="mt-1 text-sm text-slate-600"><span class="text-red-700">*</span> Campo obligatorio.</p>
        </div>

        <div class="grid gap-6 p-5 sm:grid-cols-2 sm:p-6">
            <div>
                <label for="first_names" class="block text-sm font-semibold text-slate-800">Nombres <span class="text-red-700" aria-hidden="true">*</span></label>
                <input id="first_names" name="first_names" type="text" maxlength="120" required autocomplete="given-name" value="{{ old('first_names', $patient->first_names ?? '') }}" @class(['mt-2 min-h-11 w-full rounded-xl border bg-white px-3 py-2 text-slate-950 shadow-sm', 'border-red-400' => $errors->has('first_names'), 'border-slate-300' => ! $errors->has('first_names')]) aria-describedby="first_names_error">
                @error('first_names')<p id="first_names_error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="last_names" class="block text-sm font-semibold text-slate-800">Apellidos <span class="text-red-700" aria-hidden="true">*</span></label>
                <input id="last_names" name="last_names" type="text" maxlength="120" required autocomplete="family-name" value="{{ old('last_names', $patient->last_names ?? '') }}" @class(['mt-2 min-h-11 w-full rounded-xl border bg-white px-3 py-2 text-slate-950 shadow-sm', 'border-red-400' => $errors->has('last_names'), 'border-slate-300' => ! $errors->has('last_names')]) aria-describedby="last_names_error">
                @error('last_names')<p id="last_names_error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="dni" class="block text-sm font-semibold text-slate-800">DNI <span class="font-normal text-slate-500">(opcional)</span></label>
                <input id="dni" name="dni" type="text" maxlength="8" inputmode="numeric" autocomplete="off" value="{{ old('dni', $patient->dni ?? '') }}" @class(['mt-2 min-h-11 w-full rounded-xl border bg-white px-3 py-2 text-slate-950 shadow-sm', 'border-red-400' => $errors->has('dni'), 'border-slate-300' => ! $errors->has('dni')]) aria-describedby="dni_help dni_error">
                <p id="dni_help" class="mt-2 text-sm text-slate-500">Exactamente 8 dígitos. Conserva los ceros iniciales.</p>
                @error('dni')<p id="dni_error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="whatsapp_phone" class="block text-sm font-semibold text-slate-800">Teléfono de WhatsApp <span class="text-red-700" aria-hidden="true">*</span></label>
                <input id="whatsapp_phone" name="whatsapp_phone" type="tel" maxlength="24" required autocomplete="tel" value="{{ old('whatsapp_phone', $patient->whatsapp_phone ?? '') }}" @class(['mt-2 min-h-11 w-full rounded-xl border bg-white px-3 py-2 text-slate-950 shadow-sm', 'border-red-400' => $errors->has('whatsapp_phone'), 'border-slate-300' => ! $errors->has('whatsapp_phone')]) aria-describedby="phone_help phone_error">
                <p id="phone_help" class="mt-2 text-sm text-slate-500">Ejemplo: 987 654 321. Se guardará como +51987654321.</p>
                @error('whatsapp_phone')<p id="phone_error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="whatsapp_consented_on" class="block text-sm font-semibold text-slate-800">Fecha de consentimiento <span class="font-normal text-slate-500">(opcional)</span></label>
                <input id="whatsapp_consented_on" name="whatsapp_consented_on" type="date" max="{{ now('America/Lima')->toDateString() }}" value="{{ old('whatsapp_consented_on', isset($patient) ? $patient->whatsapp_consented_on?->toDateString() : '') }}" @class(['mt-2 min-h-11 w-full rounded-xl border bg-white px-3 py-2 text-slate-950 shadow-sm', 'border-red-400' => $errors->has('whatsapp_consented_on'), 'border-slate-300' => ! $errors->has('whatsapp_consented_on')]) aria-describedby="consent_help consent_error">
                <p id="consent_help" class="mt-2 text-sm text-slate-500">Déjala vacía si el paciente aún no autorizó mensajes.</p>
                @error('whatsapp_consented_on')<p id="consent_error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-semibold text-slate-800">Estado <span class="text-red-700" aria-hidden="true">*</span></label>
                <select id="status" name="status" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-950 shadow-sm">
                    <option value="active" @selected(old('status', $patient->status ?? 'active') === 'active')>Activo</option>
                    <option value="inactive" @selected(old('status', $patient->status ?? 'active') === 'inactive')>Inactivo</option>
                </select>
                <p class="mt-2 text-sm text-slate-500">Un paciente inactivo no podrá recibir recordatorios.</p>
                @error('status')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </x-card>

    @if ($editing && $patient->whatsapp_consented_on && old('whatsapp_consented_on', $patient->whatsapp_consented_on?->toDateString()) === '')
        <label class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
            <input type="checkbox" name="consent_removal_confirmed" value="1" class="mt-0.5 size-4 rounded border-amber-400" @checked(old('consent_removal_confirmed'))>
            <span>Confirmo que deseo retirar el consentimiento. El paciente no podrá recibir futuros mensajes.</span>
        </label>
        @error('consent_removal_confirmed')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
    @endif

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $editing ? route('patients.show', $patient) : route('patients.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancelar</a>
        <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 disabled:cursor-wait disabled:opacity-60">
            <span x-show="!submitting">{{ $editing ? 'Guardar cambios' : 'Registrar paciente' }}</span>
            <span x-cloak x-show="submitting">Guardando…</span>
        </button>
    </div>
</form>
