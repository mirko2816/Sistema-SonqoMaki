# CU-ENV-001 - Consultar historial tecnico de recordatorios

## Objetivo

Permitir que el especialista compruebe si cada ejecucion programada fue omitida o si la solicitud fue aceptada o fallo tecnicamente.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.

## Flujo principal

1. El especialista abre el historial de recordatorios.
2. El sistema lista las ejecuciones en orden cronologico de mas reciente a mas antiguo.
3. Para cada ejecucion muestra fecha y hora, paciente, plan, recordatorio y resultado: omitido, aceptado o fallido.
4. El especialista abre una ejecucion.
5. El sistema muestra el identificador devuelto por WhatsApp y el codigo o detalle de error cuando existan.

## Flujo alternativo

- Si no existen ejecuciones, el sistema muestra un estado vacio que no se confunde con un fallo.

## Excepciones (si aplica)

- Si un registro referenciado ya no esta disponible, el sistema muestra solo la informacion tecnica que aun pueda conservarse sin inventar datos.

## Postcondiciones

- No se envia ningun mensaje ni se modifica el historial.

## Reglas del negocio relacionadas

- RF-WPP-003, RF-WPP-004, RF-WPP-006.
- RNF-OPE-003, RNF-DAT-003.
