# CU-REC-002 - Ejecutar envio programado

## Objetivo

Procesar una ejecucion programada, enviar como maximo un recordatorio valido por WhatsApp y registrar su resultado tecnico.

## Actores

- Reloj del sistema.
- WhatsApp Cloud API.

## Precondiciones

- Existe una programacion cuyo dia y hora corresponden al momento actual en `America/Lima`.
- La integracion se encuentra configurada mediante variables seguras del entorno.

## Flujo principal

1. El reloj inicia el procesamiento de la ejecucion programada.
2. El sistema obtiene de forma atomica el derecho a procesar la combinacion plan, fecha y horario.
3. El sistema verifica que el paciente este activo, tenga telefono valido y consentimiento registrado.
4. El sistema verifica que el plan este activo, dentro de su rango y con recordatorios activos.
5. El sistema verifica cobertura continua y que exista exactamente una rutina vigente con al menos un ejercicio.
6. El sistema verifica que el enlace publico dirija a contenido util.
7. El sistema compone el mensaje aprobado con nombre y enlace del plan.
8. El sistema solicita el envio a WhatsApp Cloud API.
9. El sistema registra fecha y hora, paciente, plan, recordatorio, identificador externo si existe, estado tecnico y error si existe.

## Flujo alternativo

- Si la fecha ya supero el fin del plan, el sistema lo trata como finalizado y no envia.
- Si otra instancia ya proceso la misma combinacion plan, fecha y horario, la ejecucion termina sin un segundo intento.

## Excepciones (si aplica)

- Si falla cualquier condicion previa al envio, el sistema no llama a WhatsApp y registra o expone un motivo operativo claro para corregir la configuracion.
- Si WhatsApp rechaza la solicitud o no responde correctamente, el sistema registra el intento como fallido con el detalle disponible.
- El sistema no realiza reintentos automaticos.

## Postcondiciones

- Existe como maximo un intento por plan, fecha y horario.
- Si se llamo a WhatsApp, el resultado tecnico aceptado o fallido queda consultable.
- Un resultado aceptado no se interpreta como leido ni realizado.

## Reglas del negocio relacionadas

- RN-RUT-003, RN-REC-002, RN-REC-003.
- RF-REC-006 a RF-REC-009.
- RF-WPP-001 a RF-WPP-005.
