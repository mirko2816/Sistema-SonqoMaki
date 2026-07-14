# CU-PAC-005 - Eliminar paciente

## Objetivo

Eliminar definitivamente un paciente y su informacion operativa dependiente mediante confirmacion explicita.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.
- El paciente existe.

## Flujo principal

1. El especialista selecciona `Eliminar paciente`.
2. El sistema enumera los datos dependientes que se eliminaran: planes, rutinas configuradas, recordatorios, enlaces e historial tecnico.
3. El sistema aclara que la biblioteca de ejercicios no sera eliminada.
4. El especialista confirma explicitamente la eliminacion definitiva.
5. El sistema elimina al paciente y toda su informacion operativa dependiente en una unica operacion consistente.
6. El sistema vuelve al listado y confirma el resultado.

## Flujo alternativo

- Si el especialista cancela, el sistema no modifica ningun dato.

## Excepciones (si aplica)

- Si cualquier parte de la eliminacion falla, el sistema revierte la operacion completa y comunica que no se elimino el paciente.

## Postcondiciones

- El paciente y sus dependencias ya no existen.
- Su telefono normalizado puede utilizarse en un nuevo registro.
- Los ejercicios reutilizables permanecen en la biblioteca.

## Reglas del negocio relacionadas

- RN-PAC-001.
- RF-PAC-007, RF-PAC-008.
