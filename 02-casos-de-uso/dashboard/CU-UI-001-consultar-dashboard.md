# CU-UI-001 - Consultar dashboard operativo

## Objetivo

Ofrecer al especialista un resumen simple para acceder rapidamente al trabajo del MVP.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.

## Flujo principal

1. El especialista abre el dashboard.
2. El sistema muestra por paciente, como minimo, nombre, telefono, estado de cada plan y estado de sus recordatorios.
3. El especialista selecciona un paciente o plan.
4. El sistema abre el detalle correspondiente.

## Flujo alternativo

- Si no existen pacientes, el sistema muestra un estado vacio con acceso a `Nuevo paciente`.
- Si un paciente tiene varios planes, el sistema muestra cada plan por separado sin fusionar sus estados.

## Excepciones (si aplica)

- Si un dato cambia durante la consulta, el sistema actualiza el resumen al recargar sin presentar estados combinados de planes distintos.

## Postcondiciones

- No se modifica ningun dato.

## Reglas del negocio relacionadas

- RN-PLA-001, RN-PLA-002.
- RF-UI-002.
