# CU-AUT-001 - Iniciar sesion

## Objetivo

Permitir que el especialista acceda a las funciones privadas con su email y contrasena.

## Actores

- Especialista.

## Precondiciones

- La cuenta fue creada manualmente y esta habilitada.
- El especialista no tiene una sesion autenticada vigente.

## Flujo principal

1. El especialista abre la pantalla de inicio de sesion.
2. El sistema solicita email y contrasena.
3. El especialista envia ambos campos.
4. El sistema valida que los campos tengan formato admisible.
5. El sistema compara las credenciales con la cuenta configurada.
6. El sistema crea una sesion protegida y redirige al dashboard.

## Flujo alternativo

- Si ya existe una sesion vigente, el sistema abre directamente el dashboard.

## Excepciones (si aplica)

- Si falta un campo o su formato es invalido, el sistema indica el campo a corregir.
- Si las credenciales no coinciden o la cuenta no esta habilitada, el sistema muestra un mensaje generico y no crea la sesion.

## Postcondiciones

- El especialista queda autenticado o el estado previo permanece sin cambios.

## Reglas del negocio relacionadas

- RF-AUT-001, RF-AUT-003, RF-AUT-004.
- RNF-SEG-001, RNF-SEG-002.
