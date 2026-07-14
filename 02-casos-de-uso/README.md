# Casos de uso del MVP - Sonqo Maki

> Estado: depurados para implementacion  
> Fecha: 13 de julio de 2026  
> Fuentes: vision y alcance del producto, requisitos funcionales y reglas de negocio vigentes.

## Criterio de seleccion

Este catalogo conserva solo interacciones necesarias para completar, proteger o verificar el flujo principal del MVP. Cada caso describe un resultado observable, validaciones y referencias trazables, sin asumir framework, base de datos ni arquitectura.

## Actores

- **Especialista:** unico usuario autenticado. Su cuenta se crea manualmente fuera de la interfaz.
- **Paciente:** actor sin cuenta que abre el enlace recibido por WhatsApp.
- **Reloj del sistema:** inicia las ejecuciones programadas.
- **WhatsApp Cloud API:** acepta o rechaza tecnicamente cada solicitud de envio.

## Catalogo vigente

| Modulo         | Casos de uso                                           |
|:-------------- |:------------------------------------------------------ |
| Autenticacion  | Iniciar sesion; cerrar sesion                          |
| Pacientes      | Registrar; consultar; editar; cambiar estado; eliminar |
| Ejercicios     | Crear; consultar y seleccionar; editar                 |
| Planes         | Crear; editar; duplicar; cambiar estado                |
| Rutinas        | Configurar rutinas y sus ejercicios                    |
| Pagina publica | Consultar rutina vigente                               |
| Recordatorios  | Configurar; ejecutar envio programado                  |
| Envios         | Consultar historial tecnico                            |
| Dashboard      | Consultar resumen operativo                            |

## Convenciones para implementacion

- Las fechas se interpretan como dias calendario inclusivos en `America/Lima`.
- Todo plan nuevo o duplicado se guarda inicialmente `en pausa`; solo pasa a `activo` despues de validar su configuracion completa. Esto evita introducir un cuarto estado `borrador` no definido para el MVP.
- Las validaciones fallidas no producen cambios parciales.
- "Eliminar" significa eliminacion definitiva cuando el caso lo indica.
- "Aceptado" por WhatsApp es un resultado tecnico; no demuestra entrega, lectura ni cumplimiento.
- Los identificadores internos nunca se exponen como tokens publicos predecibles.
