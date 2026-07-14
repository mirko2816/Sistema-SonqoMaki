# CU-PAC-001 - Registrar paciente

## Objetivo

Registrar los datos minimos de un paciente y la evidencia de su consentimiento para WhatsApp.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.
- El paciente comunico previamente si autoriza recibir recordatorios por WhatsApp.

## Flujo principal

1. El especialista selecciona `Nuevo paciente`.
2. El sistema solicita nombres, apellidos, DNI, telefono WhatsApp peruano, estado y fecha de consentimiento.
3. El especialista completa los datos y confirma el registro.
4. El sistema elimina separadores del telefono y lo normaliza a `+51` seguido de nueve digitos.
5. El sistema valida campos obligatorios, formato, DNI no repetido y telefono normalizado no repetido.
6. El sistema guarda al paciente y muestra su detalle.

## Flujo alternativo

- Si el paciente aun no dio consentimiento, el especialista omite la fecha; el paciente se registra, pero no puede recibir recordatorios.

## Excepciones (si aplica)

- Si el DNI o telefono ya pertenece a otro paciente activo o inactivo, el sistema rechaza el registro e identifica el conflicto.
- Si un dato es invalido, el sistema conserva los valores ingresados y solicita corregirlos.

## Postcondiciones

- Existe un paciente con estado activo o inactivo y, cuando corresponda, su fecha de consentimiento.
- No se crean planes ni recordatorios automaticamente.

## Reglas del negocio relacionadas

- RN-PAC-001.
- RF-PAC-001, RF-PAC-002, RF-PAC-009.
