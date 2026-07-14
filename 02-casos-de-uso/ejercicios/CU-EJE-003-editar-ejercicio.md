# CU-EJE-003 - Editar ejercicio

## Objetivo

Actualizar un ejercicio reutilizable de la biblioteca.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.
- El ejercicio existe en la biblioteca.

## Flujo principal

1. El especialista abre el ejercicio y selecciona `Editar`.
2. El sistema muestra sus datos actuales.
3. El especialista modifica los campos y confirma.
4. El sistema aplica las mismas validaciones usadas al crear un ejercicio.
5. El sistema guarda y muestra el ejercicio actualizado.

## Flujo alternativo

- El especialista cancela y el sistema conserva los datos anteriores.

## Excepciones (si aplica)

- Si los datos son invalidos, el sistema no guarda y solicita corregirlos.

## Postcondiciones

- La biblioteca contiene la version actualizada.
- Las configuraciones ya copiadas a rutinas conservan sus valores, evitando cambios retroactivos en planes de pacientes.

## Reglas del negocio relacionadas

- RF-EJE-003, RF-EJE-006.
- Principio de independencia entre biblioteca y configuracion de rutina de RF-RUT-006.
