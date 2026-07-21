<x-layouts.authenticated title="Editar plantilla">
    <x-page-header title="Editar plantilla" description="Los cambios afectan esta plantilla, no los ejercicios originales ni copias futuras ya creadas." />
    <div class="mt-8">@include('routine-templates._form', ['routineTemplate' => $routineTemplate])</div>
</x-layouts.authenticated>
