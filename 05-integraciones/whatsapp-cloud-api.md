# Integración con WhatsApp Cloud API

## Propósito

Enviar recordatorios reales desde la ejecución local del MVP y registrar el resultado inmediato de cada solicitud.

## Alcance

- Enviar una plantilla aprobada por Meta al teléfono normalizado del paciente.
- Incluir el nombre del paciente y el enlace público exclusivo de su plan.
- Registrar el identificador externo, código HTTP y error sanitizado cuando existan.
- Clasificar la ejecución como `accepted` o `failed` después de contactar al proveedor.

No se recibirán webhooks ni se registrarán estados posteriores de entrega o lectura. Tampoco se procesarán respuestas del paciente ni se ejecutarán reintentos automáticos.

## Condiciones previas

- La aplicación, PostgreSQL y el scheduler están activos.
- La computadora tiene conexión a Internet.
- Las credenciales de Meta están configuradas mediante variables de entorno no versionadas.
- Existe una plantilla aprobada y compatible con el mensaje configurado.
- El plan, paciente, rutina, recordatorio y enlace cumplen las reglas de envío.

## Resultado inmediato

`accepted` significa solamente que WhatsApp aceptó técnicamente la solicitud. No demuestra entrega, lectura ni realización de la rutina.

Si una validación interna impide contactar a WhatsApp, la ejecución se registra como `omitted` con un motivo normalizado. Si WhatsApp rechaza la solicitud o responde incorrectamente, se registra como `failed`.

## Seguridad

- No versionar access tokens, secretos ni credenciales.
- No registrar el token público completo ni datos de autenticación.
- Sanitizar respuestas y errores antes de persistirlos o escribirlos en logs.
- Usar HTTPS para el enlace público expuesto mediante el túnel.

## Pendientes operativos

Antes de una prueba real deben definirse y configurarse la cuenta de Meta, el número emisor, la plantilla aprobada y la URL pública estable durante la prueba.
