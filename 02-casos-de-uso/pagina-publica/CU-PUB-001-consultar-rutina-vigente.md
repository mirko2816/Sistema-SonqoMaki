# CU-PUB-001 - Consultar rutina vigente

## Objetivo

Permitir que el paciente consulte sin cuenta el estado actual de su plan y, cuando corresponda, unicamente su rutina vigente mediante cualquier enlace seguro previamente generado para ese plan.

## Actores

- Paciente.

## Precondiciones

- El paciente dispone del enlace publico generado automaticamente para el plan.

## Flujo principal

1. El paciente abre el enlace desde su dispositivo.
2. El sistema valida que el token sea autentico, no predecible y corresponda a un plan existente.
3. El sistema evalua el estado y rango del plan usando la fecha de `America/Lima`.
4. El sistema comprueba que el plan este activo y que la fecha actual este dentro de su rango inclusivo.
5. El sistema identifica la unica rutina vigente del dia comparando sus fechas inicial y final inclusivas con la fecha actual.
6. El sistema dirige a la pagina publica de esa rutina y muestra su nombre, ejercicios en orden, indicaciones configuradas y enlaces externos disponibles.

## Flujo alternativo

- Si el plan esta en pausa, el sistema muestra una pagina estatica con el mensaje: "Su plan de ejercicios se encuentra pausado. Comuniquese con el especialista encargado".
- Si el plan esta finalizado o la fecha actual ya supero su fecha final, el sistema muestra una pagina estatica con el mensaje: "Plan de ejercicios finalizado. Para mas consultas, comuniquese con el especialista encargado".

## Excepciones (si aplica)

- Si el token es invalido, revocado o no corresponde a un plan, el sistema muestra un estado generico sin datos personales.
- Si la fecha actual es anterior al inicio de un plan activo, el sistema muestra un estado no disponible y no expone rutinas futuras.
- Si un plan activo no tiene exactamente una rutina vigente, el sistema muestra un estado no disponible y no expone rutinas pasadas ni futuras.
- Si un recurso externo falla, la pagina mantiene visibles las instrucciones del ejercicio y comunica que el recurso no esta disponible.

## Postcondiciones

- No se modifica el plan ni se registra cumplimiento.
- El paciente no ve datos personales ni rutinas pasadas o futuras.

## Reglas del negocio relacionadas

- RN-RUT-003.
- RN-PUB-001.
- RF-PUB-001 a RF-PUB-008.
- RNF-SEG-004, RNF-SEG-005, RNF-PRI-002.
