# CU-RUT-003 - Archivar plantilla de rutina

## Objetivo

Retirar una plantilla de rutina del listado activo de la biblioteca y moverla a una sección de archivados, sin eliminar las copias ya generadas en planes de pacientes.

## Actores

- Especialista.

## Precondiciones

- El especialista inició sesión.
- La plantilla existe en la biblioteca con estado `active`.

## Flujo principal

1. El especialista selecciona una plantilla de la biblioteca y solicita archivarla.
2. El sistema muestra un mensaje de confirmación indicando que:
   - la plantilla pasará al estado `archived` y dejará de aparecer en la lista principal;
   - las copias ya generadas en planes de pacientes no se modifican;
   - la plantilla podrá restaurarse desde la sección de archivados.
3. El especialista confirma el archivado.
4. El sistema actualiza `routine_templates.status` a `archived`.
5. La plantilla desaparece del listado principal de la biblioteca.
6. La plantilla aparece en la sección separada de archivados.

## Flujo alternativo

- Si el especialista cancela la confirmación, la plantilla permanece en estado `active` sin cambios.
- Si el especialista accede a la sección de archivados y solicita restaurar una plantilla, el sistema actualiza `status` a `active` y la plantilla vuelve al listado principal.

## Excepciones (si aplica)

- Si la plantilla ya está en estado `archived`, el sistema no permite archivarla nuevamente y ofrece la opción de restaurarla.

## Postcondiciones

- La plantilla archivada no aparece en el listado principal ni puede seleccionarse para crear rutinas en nuevos planes.
- La plantilla es visible y gestionable desde la sección de archivados.
- Las rutinas ya generadas a partir de esta plantilla en planes de pacientes permanecen intactas y operativas.

## Reglas del negocio relacionadas

- RF-RUT-008.
