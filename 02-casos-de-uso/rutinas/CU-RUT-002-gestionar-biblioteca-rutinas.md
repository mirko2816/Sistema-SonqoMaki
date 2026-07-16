# CU-RUT-002 - Gestionar biblioteca de rutinas

## Objetivo

Crear y mantener plantillas de rutina reutilizables para reducir el trabajo repetitivo al configurar planes.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.
- Los ejercicios que se desean agregar existen en la biblioteca de ejercicios.

## Flujo principal

1. El especialista abre la biblioteca de rutinas y selecciona `Nueva plantilla`.
2. El sistema solicita un nombre y permite seleccionar ejercicios de la biblioteca.
3. El especialista ordena los ejercicios y ajusta sus indicaciones dentro de la plantilla.
4. El sistema valida el nombre, el orden y los valores configurados.
5. El sistema guarda la plantilla y la deja disponible al configurar cualquier plan.

## Flujo alternativo

- El especialista puede consultar, editar o archivar una plantilla existente.
- Al usar una plantilla dentro de un plan, el sistema crea una copia independiente de la rutina y sus ejercicios.

## Excepciones

- Si la plantilla no tiene nombre o no contiene ejercicios validos, el sistema no permite utilizarla como base de un plan.
- Si una plantilla se archiva, las copias creadas previamente dentro de planes permanecen sin cambios.

## Postcondiciones

- La biblioteca contiene una plantilla reutilizable valida o conserva su estado anterior.
- Ningun plan existente cambia como consecuencia de editar o archivar una plantilla.

## Reglas del negocio relacionadas

- RF-RUT-008.
- RF-EJE-004, RF-EJE-006.
