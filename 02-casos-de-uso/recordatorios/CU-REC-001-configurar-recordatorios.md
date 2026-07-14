# CU-REC-001 - Configurar recordatorios

## Objetivo

Definir por plan los dias y horarios de envio, con un maximo de dos horarios distintos por dia.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.
- El plan existe.

## Flujo principal

1. El especialista abre los recordatorios del plan.
2. El sistema muestra la configuracion actual por dia de la semana y su estado activo o pausado.
3. El especialista selecciona los dias y agrega hasta dos horarios por cada dia.
4. El sistema interpreta los horarios en `America/Lima` y valida limite y unicidad por plan, dia y hora.
5. El especialista confirma.
6. El sistema guarda la programacion.

## Flujo alternativo

- El especialista puede editar horarios existentes o pausar todos los recordatorios del plan sin cambiar el estado del plan.
- Al reactivar recordatorios, solo se consideran ejecuciones futuras; no se envian recordatorios omitidos.

## Excepciones (si aplica)

- Si un dia supera dos horarios o repite uno, el sistema rechaza la configuracion e identifica el conflicto.
- Si el plan esta incompleto, el sistema puede guardar los horarios, pero informa que no se ejecutaran hasta que el plan pueda activarse.

## Postcondiciones

- El plan conserva una programacion valida, activa o pausada.
- No se envia ningun mensaje como parte de este caso.

## Reglas del negocio relacionadas

- RN-REC-001.
- RF-REC-001 a RF-REC-005.
- RNF-OPE-005.
