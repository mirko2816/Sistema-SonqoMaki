# CU-PAC-002 - Consultar pacientes

## Objetivo

Encontrar pacientes y consultar sus datos basicos, estado y planes asociados.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.

## Flujo principal

1. El especialista abre `Pacientes`.
2. El sistema muestra el listado de pacientes registrados con datos suficientes para distinguirlos.
3. El especialista selecciona un paciente.
4. El sistema muestra sus datos de contacto, consentimiento, estado y planes asociados.

## Flujo alternativo

- Si no existen pacientes, el sistema muestra un estado vacio y la accion para registrar el primero.

## Excepciones (si aplica)

- Si el paciente fue archivado antes de abrir su detalle, el sistema informa que ya no está disponible en el listado operativo y actualiza la vista.

## Postcondiciones

- No se modifica ningun dato.

## Reglas del negocio relacionadas

- RF-PAC-003, RF-PAC-004.
- RF-PLA-004.
