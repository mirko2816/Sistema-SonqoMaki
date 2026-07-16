# CU-PAC-005 - Archivar paciente

## Objetivo

Archivar un paciente mediante eliminacion logica sin perder su informacion operativa ni el historial tecnico.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.
- El paciente existe.

## Flujo principal

1. El especialista selecciona `Archivar paciente`.
2. El sistema explica que el paciente dejara de aparecer en las consultas normales, sus planes quedaran pausados, sus recordatorios se desactivaran y sus enlaces se revocaran.
3. El sistema aclara que se conservaran su configuracion y el historial tecnico, y que las bibliotecas no cambiaran.
4. El especialista confirma explicitamente el archivo.
5. El sistema marca al paciente como archivado y aplica los efectos indicados en una unica operacion consistente.
6. El sistema vuelve al listado y confirma el resultado.

## Flujo alternativo

- Si el especialista cancela, el sistema no modifica ningun dato.

## Excepciones (si aplica)

- Si cualquier parte del archivo falla, el sistema revierte la operacion completa y comunica que no se archivo el paciente.

## Postcondiciones

- El paciente y sus dependencias dejan de aparecer en las consultas normales, pero permanecen recuperables mediante una operacion tecnica.
- Su telefono y su DNI, cuando exista, permanecen reservados.
- Las bibliotecas de ejercicios y rutinas permanecen sin cambios.

## Reglas del negocio relacionadas

- RN-PAC-001.
- RF-PAC-007, RF-PAC-008.
