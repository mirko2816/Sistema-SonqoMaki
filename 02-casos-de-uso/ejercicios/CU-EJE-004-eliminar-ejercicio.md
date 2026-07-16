# CU-EJE-004 - Eliminar ejercicio de la biblioteca

## Objetivo

Retirar un ejercicio de la biblioteca para que no pueda seleccionarse en nuevas rutinas, preservando las copias ya configuradas en planes existentes.

## Actores

- Especialista.

## Precondiciones

- El especialista inició sesión.
- El ejercicio existe en la biblioteca (no ha sido eliminado previamente).

## Flujo principal

1. El especialista selecciona un ejercicio de la biblioteca y solicita eliminarlo.
2. El sistema muestra un mensaje de confirmación indicando que:
   - el ejercicio dejará de aparecer en la biblioteca y no podrá seleccionarse para nuevas rutinas;
   - las copias ya configuradas en rutinas de planes existentes no se modifican ni eliminan.
3. El especialista confirma la eliminación.
4. El sistema aplica la eliminación lógica (`deleted_at`) al ejercicio.
5. El ejercicio desaparece del listado de la biblioteca.

## Flujo alternativo

- Si el especialista cancela la confirmación, el ejercicio permanece sin cambios.

## Excepciones (si aplica)

- No aplica. La eliminación no genera conflictos con las copias existentes porque estas son independientes de la biblioteca.

## Postcondiciones

- El ejercicio no aparece en el listado ni puede seleccionarse para nuevas rutinas.
- Las filas existentes en `routine_exercises` y `routine_template_exercises` que referencian al ejercicio como `source_exercise_id` conservan todos sus datos y siguen siendo operativas.

## Reglas del negocio relacionadas

- RF-EJE-007.
