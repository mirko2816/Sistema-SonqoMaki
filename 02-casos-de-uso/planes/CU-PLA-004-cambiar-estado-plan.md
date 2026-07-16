# CU-PLA-004 - Cambiar estado del plan

## Objetivo

Activar, pausar, reactivar o finalizar un plan y aplicar el efecto correspondiente en recordatorios y pagina publica.

## Actores

- Especialista.
- Reloj del sistema, para la finalizacion por fecha.

## Precondiciones

- Para una accion manual, el especialista inicio sesion y el plan existe.

## Flujo principal

1. El especialista solicita una transicion permitida: de `en pausa` a `activo`, de `activo` a `en pausa` o `finalizado`, o de `en pausa` a `finalizado`.
2. El sistema muestra el efecto del cambio.
3. Para activar, el sistema valida fechas, cobertura continua, ausencia de superposiciones y al menos un ejercicio por rutina.
4. El especialista confirma.
5. En la primera activacion valida, el servidor genera automaticamente un enlace seguro exclusivo para el plan; en activaciones posteriores reutiliza el enlace vigente del mismo plan.
6. El sistema actualiza el estado y la representacion publica del plan.
7. Si queda en pausa o finalizado, el sistema impide nuevos envios.

## Flujo alternativo

- Al superar la fecha final, el sistema trata el plan como finalizado y deja de enviar, aunque la actualizacion persistida del estado se ejecute en el mismo proceso o en una tarea programada.
- Al reactivar un plan pausado, los recordatorios solo operan desde ese momento; no se recuperan envios omitidos.

## Excepciones (si aplica)

- Si el plan no cumple las validaciones de activacion, el sistema mantiene su estado anterior y muestra todos los conflictos detectados.
- Si el plan ya esta finalizado, el sistema rechaza cualquier intento de reactivarlo o pausarlo y ofrece duplicarlo.

## Postcondiciones

- Plan activo: puede participar en envios si cumple las demas condiciones.
- Plan en pausa: no envia y su pagina publica informa la pausa.
- Plan finalizado: no envia y su pagina publica informa la conclusion.
- El estado finalizado es irreversible.

## Reglas del negocio relacionadas

- RN-RUT-001, RN-RUT-002, RN-RUT-003, RN-REC-002.
- RF-PLA-002, RF-PLA-005, RF-PUB-005, RF-PUB-006, RF-REC-008.
