# CU-RUT-001 - Configurar rutinas del plan

## Objetivo

Definir la cobertura temporal del plan mediante rutinas contiguas y configurar los ejercicios de cada una.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.
- El plan existe.
- Los ejercicios que se desean agregar existen en la biblioteca.

## Flujo principal

1. El especialista abre la configuracion de rutinas del plan.
2. El sistema muestra el rango inclusivo del plan y las rutinas existentes en orden cronologico.
3. El especialista crea o edita una rutina indicando nombre, fecha inicial y fecha final.

   > **Nota:** el nombre de la rutina es visible para el paciente en la página pública. La interfaz debe mostrarlo claramente para que el especialista elija un nombre comprensible (por ejemplo: "Semana 1 — Movilidad de hombro") en lugar de un nombre de uso interno.

4. El sistema valida que la rutina esté dentro del plan y no se superponga con otra.
5. El especialista selecciona uno o mas ejercicios de la biblioteca.
6. El sistema copia cada seleccion a la rutina.
7. El especialista ordena los ejercicios y ajusta sets, repeticiones, duracion o URL para esa rutina.
8. El sistema valida los valores y guarda la configuracion.
9. El sistema muestra si todo el rango del plan esta cubierto sin huecos y si cada rutina tiene ejercicios.

## Flujo alternativo

- El especialista puede editar o quitar rutinas y ejercicios mientras configura el plan.
- Se permite guardar temporalmente una configuracion incompleta si el plan queda `en pausa`.
- Al cambiar valores de un ejercicio dentro de la rutina, el ejercicio original de la biblioteca no cambia.
- El especialista puede partir de una plantilla de la biblioteca; el sistema copia la rutina y sus ejercicios al plan sin mantener dependencia editable con la plantilla.

## Excepciones (si aplica)

- Si una rutina queda fuera del plan, se superpone o tiene fechas invertidas, el sistema rechaza esa modificacion e identifica las fechas en conflicto.
- Si se intenta activar el plan con huecos, superposiciones o una rutina sin ejercicios, el sistema bloquea la activacion y muestra todos los problemas.

## Postcondiciones

- Las rutinas validas quedan asociadas solo a ese plan.
- Para cada fecha de un plan util existe exactamente una rutina vigente.
- El orden y valores especificos de ejercicios quedan persistidos por rutina.

## Reglas del negocio relacionadas

- RN-RUT-001, RN-RUT-002, RN-RUT-003.
- RF-RUT-001 a RF-RUT-007.
