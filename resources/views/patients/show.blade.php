<x-layouts.authenticated :title="$patient->full_name">
    <x-page-header :title="$patient->full_name" description="Consulta los datos básicos y administra el estado del paciente.">
        <x-slot:actions><a href="{{ route('patients.edit', $patient) }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 sm:w-auto">Editar datos</a></x-slot:actions>
    </x-page-header>

    <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_21rem]">
        <div class="space-y-6">
            <x-card>
                <div class="border-b border-slate-200 px-5 py-5 sm:px-6"><h2 class="text-lg font-semibold text-slate-950">Información del paciente</h2></div>
                <dl class="grid gap-x-8 gap-y-6 p-5 sm:grid-cols-2 sm:p-6">
                    <div><dt class="text-sm font-medium text-slate-500">Nombre completo</dt><dd class="mt-1 font-semibold text-slate-950">{{ $patient->full_name }}</dd></div>
                    <div><dt class="text-sm font-medium text-slate-500">Estado</dt><dd class="mt-2"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $patient->status === 'active', 'bg-slate-200 text-slate-700' => $patient->status === 'inactive'])>{{ $patient->status === 'active' ? 'Activo' : 'Inactivo' }}</span></dd></div>
                    <div><dt class="text-sm font-medium text-slate-500">Teléfono de WhatsApp</dt><dd class="mt-1 text-slate-900">{{ $patient->whatsapp_phone }}</dd></div>
                    <div><dt class="text-sm font-medium text-slate-500">DNI</dt><dd class="mt-1 text-slate-900">{{ $patient->dni ?: 'No registrado' }}</dd></div>
                    <div><dt class="text-sm font-medium text-slate-500">Consentimiento para mensajes</dt><dd class="mt-1 text-slate-900">{{ $patient->whatsapp_consented_on?->translatedFormat('d \\d\\e F \\d\\e Y') ?? 'No registrado' }}</dd></div>
                    <div><dt class="text-sm font-medium text-slate-500">Fecha de registro</dt><dd class="mt-1 text-slate-900">{{ $patient->created_at->translatedFormat('d \\d\\e F \\d\\e Y') }}</dd></div>
                </dl>
            </x-card>
            <x-card><x-empty-state title="Planes todavía no disponibles" description="Los planes de ejercicios se incorporarán en una próxima etapa. No se ha creado información ficticia para este paciente." /></x-card>
        </div>

        <aside class="space-y-6" aria-label="Acciones del paciente">
            <x-card class="p-5">
                <h2 class="font-semibold text-slate-950">Cambiar estado</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $patient->status === 'active' ? 'Al inactivarlo no podrá recibir recordatorios. Sus datos se conservarán.' : 'Al activarlo, los futuros planes conservarán sus propios estados y condiciones.' }}</p>
                <form method="POST" action="{{ route('patients.status', $patient) }}" class="mt-4" x-data="{ submitting: false }" x-on:submit="submitting = true">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $patient->status === 'active' ? 'inactive' : 'active' }}">
                    <button type="submit" x-bind:disabled="submitting" class="min-h-11 w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:opacity-60">{{ $patient->status === 'active' ? 'Confirmar inactivación' : 'Confirmar activación' }}</button>
                </form>
                @error('status')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
            </x-card>

            <x-card class="border-red-200 p-5">
                <h2 class="font-semibold text-red-900">Archivar paciente</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Dejará de aparecer en consultas normales y quedará inactivo. Sus datos se conservarán; cuando existan planes, también se pausarán sus planes, se desactivarán recordatorios y se revocarán enlaces.</p>
                <form method="POST" action="{{ route('patients.destroy', $patient) }}" class="mt-4" x-data="{ confirmed: false, submitting: false }" x-on:submit="submitting = true">
                    @csrf @method('DELETE')
                    <label class="flex items-start gap-3 text-sm text-slate-700"><input type="checkbox" name="archive_confirmed" value="1" required x-model="confirmed" class="mt-0.5 size-4 rounded border-slate-400"><span>Confirmo que comprendo el efecto y deseo archivar a este paciente.</span></label>
                    @error('archive_confirmed')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    <button type="submit" x-bind:disabled="!confirmed || submitting" class="mt-4 min-h-11 w-full rounded-xl bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-50">Archivar paciente</button>
                </form>
            </x-card>
            <a href="{{ route('patients.index') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Volver al listado</a>
        </aside>
    </div>
</x-layouts.authenticated>
