# CU-PUB-001 - Consultar rutina vigente

## Objetivo

Permitir que el paciente consulte sin cuenta unicamente la rutina vigente de un plan mediante un enlace seguro.

## Actores

- Paciente.

## Precondiciones

- El paciente dispone del enlace publico generado automaticamente para el plan.

## Flujo principal

1. El paciente abre el enlace desde su dispositivo.
2. El sistema valida que el token sea autentico, no predecible y corresponda a un plan existente.
3. El sistema evalua el estado y rango del plan usando la fecha de `America/Lima`.
4. Si el plan esta activo, el sistema identifica la unica rutina vigente del dia.
5. El sistema muestra nombre de la rutina, ejercicios en orden, indicaciones configuradas y enlaces externos disponibles.

## Flujo alternativo

- Si el plan esta en pausa, el sistema muestra que esta en pausa y recomienda contactar al especialista.
- Si el plan esta finalizado o ya supero su fecha final, el sistema muestra que el plan concluyo.

## Excepciones (si aplica)

- Si el token es invalido, revocado o no corresponde a un plan, el sistema muestra un estado generico sin datos personales.
- Si un plan activo no tiene exactamente una rutina vigente, el sistema muestra un estado no disponible y no expone rutinas pasadas ni futuras.
- Si un recurso externo falla, la pagina mantiene visibles las instrucciones del ejercicio y comunica que el recurso no esta disponible.

## Postcondiciones

- No se modifica el plan ni se registra cumplimiento.
- El paciente no ve datos personales ni rutinas pasadas o futuras.

## Reglas del negocio relacionadas

- RN-RUT-003.
- RF-PUB-001 a RF-PUB-007.
- RNF-SEG-004, RNF-SEG-005, RNF-PRI-002.
