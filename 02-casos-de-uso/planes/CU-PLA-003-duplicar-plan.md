# CU-PLA-003 - Duplicar plan de ejercicios

## Objetivo

Reutilizar la estructura de un plan existente creando una copia independiente para otro paciente.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.
- El plan de origen existe.
- El paciente de destino existe y esta activo.

## Flujo principal

1. El especialista selecciona `Duplicar plan`.
2. El sistema solicita el paciente de destino y muestra la estructura que se copiara.
3. El especialista confirma.
4. El sistema copia datos del plan, rutinas, orden y configuracion de ejercicios.
5. El sistema crea la copia `en pausa`, con una configuracion de recordatorios vacia e inactiva, sin historial, enlace ni ejecuciones del origen.
6. El sistema muestra la copia para ajustar fechas, rutinas, ejercicios y estado, y configurar nuevos recordatorios cuando corresponda.

## Flujo alternativo

- El especialista puede duplicar hacia el mismo paciente si desea un plan adicional independiente.

## Excepciones (si aplica)

- Si el origen o destino deja de existir, el sistema no crea una copia parcial.
- Si las fechas copiadas no resultan utilizables, la copia permanece `en pausa` hasta que se corrijan.

## Postcondiciones

- Existe un nuevo plan editable sin vinculos operativos con el original.
- No se copiaron dias ni horarios de recordatorio.
- Editar o archivar una copia no afecta a la otra.

## Reglas del negocio relacionadas

- RN-PLA-001, RN-PLA-002.
- RF-PLA-006.
