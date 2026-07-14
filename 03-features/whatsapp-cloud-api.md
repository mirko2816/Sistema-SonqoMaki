# Features de WhatsApp Cloud API

> Actor externo: WhatsApp Cloud API  
> Alcance: MVP  
> Detalle de flujos y excepciones: caso de uso referenciado.

## Criterio documental

WhatsApp Cloud API es un servicio externo, no un usuario del producto. Estas features describen los resultados que la integración necesita obtener del actor; los pasos técnicos permanecen en el caso de uso y en la futura documentación de integración.

### FE-WPP-ENV-001 - Aceptar una solicitud de envío

**Para** entregar el recordatorio por el canal configurado, **el sistema necesita** enviar a WhatsApp una plantilla aprobada con el destinatario y el enlace seguro del plan.  
**Detalle:** [CU-REC-002](../02-casos-de-uso/recordatorios/CU-REC-002-ejecutar-envio-programado.md)

### FE-WPP-ENV-002 - Recibir el resultado técnico

**Para** conservar evidencia del intento, **el sistema necesita** recibir y registrar el identificador externo cuando exista y distinguir una solicitud aceptada de una fallida.  
**Detalle:** registro técnico en [CU-REC-002](../02-casos-de-uso/recordatorios/CU-REC-002-ejecutar-envio-programado.md)

### FE-WPP-ENV-003 - Conservar información del fallo

**Para** que el especialista pueda diagnosticar problemas, **el sistema necesita** guardar el código o detalle de error que WhatsApp proporcione al rechazar o no procesar correctamente la solicitud.  
**Detalle:** excepciones de [CU-REC-002](../02-casos-de-uso/recordatorios/CU-REC-002-ejecutar-envio-programado.md) y consulta en [CU-ENV-001](../02-casos-de-uso/envios/CU-ENV-001-consultar-historial-envios.md)

## Límites del actor

En el MVP, una aceptación técnica no demuestra entrega, lectura ni cumplimiento. No se procesan respuestas del paciente, estados detallados posteriores ni reintentos automáticos.

