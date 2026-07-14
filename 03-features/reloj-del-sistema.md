# Features del reloj del sistema

> Actor: reloj del sistema  
> Alcance: MVP  
> Detalle de flujos y excepciones: caso de uso referenciado.

## Criterio documental

El reloj es un actor técnico que inicia trabajo en el momento programado. Las validaciones, el envío y la persistencia son responsabilidades del sistema descritas en el caso de uso.

### FE-REL-REC-001 - Iniciar recordatorios programados

**Como** reloj del sistema, **quiero** activar cada programación cuando coincidan su día y hora en `America/Lima`, **para** que los recordatorios se procesen en el momento previsto.  
**Detalle:** [CU-REC-002](../02-casos-de-uso/recordatorios/CU-REC-002-ejecutar-envio-programado.md)

### FE-REL-REC-002 - Evitar ejecuciones duplicadas

**Como** reloj del sistema, **quiero** que una misma combinación de plan, fecha y horario se procese una sola vez, **para** evitar mensajes duplicados aunque el disparador se repita.  
**Detalle:** control de idempotencia en [CU-REC-002](../02-casos-de-uso/recordatorios/CU-REC-002-ejecutar-envio-programado.md) y [RN-REC-003](../01-requisitos/reglas-de-negocio.md#rn-rec-003-un-solo-envio-por-ejecucion-programada)

### FE-REL-PLA-001 - Reconocer planes vencidos

**Como** reloj del sistema, **quiero** reconocer cuando un plan superó su fecha final, **para** detener sus envíos aunque su estado persistido aún no se haya actualizado.  
**Detalle:** alternativa de [CU-REC-002](../02-casos-de-uso/recordatorios/CU-REC-002-ejecutar-envio-programado.md)

## Límites del actor

El reloj no decide contenido clínico, no corrige configuraciones, no recupera ejecuciones omitidas y no inicia reintentos automáticos.
