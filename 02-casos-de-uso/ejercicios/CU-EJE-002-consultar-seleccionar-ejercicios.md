# CU-EJE-002 - Consultar y seleccionar ejercicios

## Objetivo

Consultar la biblioteca y seleccionar ejercicios al configurar una rutina.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.

## Flujo principal

1. El especialista abre la biblioteca, directamente o desde una rutina.
2. El sistema lista los ejercicios con sus datos principales.
3. El especialista consulta un ejercicio.
4. Cuando accede desde una rutina, selecciona uno o mas ejercicios.
5. El sistema agrega copias configurables de esas selecciones a la rutina sin modificar los originales.

## Flujo alternativo

- Si la biblioteca esta vacia, el sistema ofrece crear el primer ejercicio.

## Excepciones (si aplica)

- Si un ejercicio deja de estar disponible durante la seleccion, el sistema informa cual no pudo agregarse y conserva las selecciones validas.

## Postcondiciones

- La consulta no cambia la biblioteca.
- Los ejercicios seleccionados quedan asociados a la rutina y pueden recibir valores especificos para ella.

## Reglas del negocio relacionadas

- RF-EJE-002, RF-EJE-004, RF-EJE-006.
- RF-RUT-004, RF-RUT-006.
