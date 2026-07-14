# CU-PLA-001 - Crear plan de ejercicios

## Objetivo

Crear para un paciente un plan independiente con rango de fechas, rutinas y recordatorios propios.

## Actores

- Especialista.

## Precondiciones

- El especialista inicio sesion.
- El paciente existe y esta activo.

## Flujo principal

1. El especialista abre el detalle del paciente y selecciona `Nuevo plan`.
2. El sistema solicita nombre, fecha de inicio y fecha de fin.
3. El especialista completa los datos.
4. El sistema valida que la fecha inicial no sea posterior a la final.
5. El sistema crea el plan `en pausa`, asociado exclusivamente al paciente.
6. El sistema conduce al especialista a configurar sus rutinas antes de activarlo.

## Flujo alternativo

- El especialista puede conservar temporalmente la configuracion incompleta en estado `en pausa`; el sistema no permite activarla ni usarla para envios.
- El rango puede superponerse con otros **planes** del mismo paciente.

## Excepciones (si aplica)

- Si el paciente esta inactivo o fue eliminado, el sistema rechaza la creacion.
- Si las fechas son invalidas, el sistema no crea el plan.

## Postcondiciones

- Existe un plan independiente `en pausa`, con sus propias rutinas, recordatorios, enlace e historial.
- No se activa hasta tener cobertura continua y al menos un ejercicio por rutina.

## Reglas del negocio relacionadas

- RN-PLA-001, RN-PLA-002, RN-RUT-003.
- RF-PLA-001, RF-PLA-004, RF-RUT-007.
