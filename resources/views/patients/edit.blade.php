<x-layouts.authenticated title="Editar paciente">
    <x-page-header title="Editar paciente" description="Actualiza los datos de {{ $patient->full_name }} sin modificar sus futuros planes." />
    <div class="mt-8">@include('patients._form')</div>
</x-layouts.authenticated>
