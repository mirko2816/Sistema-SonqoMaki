# CU-PAC-004 - Cambiar estado del paciente

## Objetivo

Activar o inactivar un paciente y controlar si puede recibir recordatorios.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.
- El paciente existe.

## Flujo principal

1. El especialista abre el detalle del paciente.
2. El especialista solicita cambiar su estado.
3. El sistema muestra el efecto del cambio y solicita confirmacion.
4. El especialista confirma.
5. El sistema cambia el estado.

## Flujo alternativo

- Al reactivar un paciente, sus planes y configuraciones conservan su propio estado; los envios solo se reanudan cuando todas las condiciones de envio se cumplen.

## Excepciones (si aplica)

- Si el paciente ya tiene el estado solicitado, el sistema no duplica la operacion.

## Postcondiciones

- Un paciente inactivo no recibe recordatorios.
- Cambiar el estado no elimina planes, rutinas, recordatorios ni historial.

## Reglas del negocio relacionadas

- RF-PAC-006.
- RN-REC-002.
