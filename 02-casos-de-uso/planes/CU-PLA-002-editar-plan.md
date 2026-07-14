# CU-PLA-002 - Editar plan de ejercicios

## Objetivo

Actualizar los datos y rango de un plan sin romper la consistencia de sus rutinas.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.
- El plan existe.

## Flujo principal

1. El especialista abre el plan y selecciona `Editar`.
2. El sistema muestra nombre, fechas, estado y resumen de rutinas.
3. El especialista modifica el nombre o el rango y confirma.
4. El sistema valida el rango y vuelve a validar todas las rutinas contra las nuevas fechas.
5. El sistema guarda los cambios si las rutinas permanecen dentro del rango, sin superposiciones y con cobertura continua.

## Flujo alternativo

- El especialista puede guardar una configuracion incompleta solo dejando el plan `en pausa`; debera corregirla antes de activarlo o enviar recordatorios.

## Excepciones (si aplica)

- Si alguna rutina queda fuera del rango, se superpone o deja un dia sin cubrir, el sistema identifica el conflicto y no mantiene el plan activo con esa configuracion.

## Postcondiciones

- El plan queda actualizado de forma consistente o conserva su version anterior.
- Otros planes del paciente no cambian.

## Reglas del negocio relacionadas

- RN-PLA-001, RN-PLA-002.
- RN-RUT-001, RN-RUT-002, RN-RUT-003.
- RF-PLA-003.
