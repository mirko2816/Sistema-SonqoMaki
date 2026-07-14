# CU-EJE-001 - Crear ejercicio

## Objetivo

Agregar a la biblioteca un ejercicio reutilizable con instrucciones y material externo opcional.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.

## Flujo principal

1. El especialista abre la biblioteca y selecciona `Nuevo ejercicio`.
2. El sistema solicita nombre y, opcionalmente, descripcion, duracion, sets, repeticiones y URL de material.
3. El especialista completa los datos y confirma.
4. El sistema valida el nombre, los valores numericos proporcionados y el formato seguro de la URL.
5. El sistema guarda el ejercicio y lo muestra en la biblioteca.

## Flujo alternativo

- El especialista puede crear el ejercicio solo con el nombre y completar los campos opcionales posteriormente.

## Excepciones (si aplica)

- Si un valor numerico no es positivo o la URL no es valida, el sistema no guarda y marca los campos a corregir.

## Postcondiciones

- El ejercicio queda disponible para seleccionarlo en cualquier rutina.
- No se almacena el contenido del recurso externo.

## Reglas del negocio relacionadas

- RF-EJE-001, RF-EJE-004, RF-EJE-005, RF-EJE-006.
