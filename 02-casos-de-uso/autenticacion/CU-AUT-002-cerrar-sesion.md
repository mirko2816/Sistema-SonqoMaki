# CU-AUT-002 - Cerrar sesion

## Objetivo

Finalizar de forma segura la sesion activa del especialista.

## Actores

- Especialista.

## Precondiciones

- El especialista tiene una sesion autenticada.

## Flujo principal

1. El especialista selecciona `Cerrar sesion`.
2. El sistema invalida la sesion activa.
3. El sistema redirige a la pantalla de inicio de sesion.

## Flujo alternativo

- No aplica.

## Excepciones (si aplica)

- Si la sesion ya vencio o no existe, el sistema dirige igualmente al inicio de sesion sin mostrar datos privados.

## Postcondiciones

- Las solicitudes posteriores requieren autenticacion.

## Reglas del negocio relacionadas

- RF-AUT-002.
- RNF-SEG-002.
