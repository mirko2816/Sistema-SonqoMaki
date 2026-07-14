# CU-PAC-003 - Editar paciente

## Objetivo

Actualizar los datos basicos o la evidencia de consentimiento de un paciente.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.
- El paciente existe.

## Flujo principal

1. El especialista abre el detalle del paciente y selecciona `Editar`.
2. El sistema muestra nombres, apellidos, DNI, telefono y fecha de consentimiento actuales.
3. El especialista modifica los campos y confirma.
4. El sistema normaliza el telefono y valida campos, formatos, DNI y telefono unicos.
5. El sistema guarda los cambios y muestra el detalle actualizado.

## Flujo alternativo

- Si se retira la fecha de consentimiento, el sistema solicita confirmacion y bloquea futuros envios al paciente.

## Excepciones (si aplica)

- Si el DNI o telefono pertenece a otro paciente, el sistema rechaza el cambio.
- Si el registro cambio desde que se abrio el formulario, el sistema evita sobrescribirlo silenciosamente y solicita recargar.

## Postcondiciones

- Los datos validos quedan actualizados; los planes y rutinas no cambian.

## Reglas del negocio relacionadas

- RN-PAC-001.
- RF-PAC-002, RF-PAC-005, RF-PAC-009.
