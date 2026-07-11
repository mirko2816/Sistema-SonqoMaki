# Visión del producto — Sonqo Maki

> Estado: propuesta en revisión para MVP  
> Fecha: 11 de julio de 2026  
> Alcance: Fase 1 — MVP

## 1. Resumen

Sonqo Maki será una aplicación web sencilla para que un especialista de la clínica registre pacientes, prepare sus planes de ejercicios y programe recordatorios que se enviarán mediante WhatsApp. Cada recordatorio incluirá un enlace seguro a una página web donde el paciente podrá consultar su rutina y los ejercicios indicados.

El MVP se concentrará en demostrar que este flujo principal funciona de extremo a extremo de forma confiable. No intentará cubrir todavía el seguimiento clínico, la comunicación bidireccional ni la administración completa de una clínica.

## 2. Problema que buscamos resolver

Después de una consulta, el paciente puede olvidar cuándo debe realizar sus ejercicios o cómo ejecutar cada uno. El especialista, por su parte, necesita una forma práctica de organizar las indicaciones y automatizar recordatorios sin preparar y enviar manualmente el mismo contenido todos los días.

Sonqo Maki busca reducir ese trabajo repetitivo y facilitar el acceso del paciente a instrucciones claras desde un enlace recibido por WhatsApp.

## 3. Visión

Facilitar que un especialista de Sonqo Maki acompañe las rutinas domiciliarias de sus pacientes mediante planes simples, instrucciones accesibles y recordatorios automáticos por WhatsApp.

## 4. Declaración de posicionamiento

Para el especialista de Sonqo Maki que necesita organizar y recordar ejercicios domiciliarios a sus pacientes, Sonqo Maki es una aplicación web de apoyo que permite crear planes y rutinas, compartirlas mediante un enlace seguro y enviar recordatorios automáticos por WhatsApp. A diferencia de un sistema clínico integral, el MVP prioriza un único flujo operativo, fácil de usar y mantener por una sola persona.

## 5. Usuarios y actores

### Usuario autenticado

- **Especialista:** único tipo de usuario del MVP. Registra pacientes, administra la biblioteca de ejercicios, crea planes y rutinas, programa recordatorios y consulta si los envíos funcionaron o fallaron.

### Actor sin cuenta

- **Paciente:** no inicia sesión ni administra información. Recibe el recordatorio por WhatsApp y abre el enlace seguro para consultar su rutina.

### Actor externo

- **WhatsApp Cloud API:** canal utilizado para enviar los recordatorios y devolver el resultado técnico del intento de envío.

## 6. Objetivo del MVP

Validar en el uso cotidiano de una clínica pequeña que un solo especialista puede:

1. registrar un paciente;
2. crearle un plan con una o más rutinas;
3. agregar ejercicios con enlaces de YouTube a cada rutina;
4. programar uno o varios recordatorios diarios;
5. enviar automáticamente por WhatsApp un enlace seguro a la rutina; y
6. comprobar si cada intento de envío fue exitoso o falló.

## 7. Alcance funcional incluido

### 7.1. Autenticación

descrito a detalle en [features/atutenticacion](features/autenticacion.md)

- Inicio y cierre de sesión del especialista.
- Un único tipo de usuario, sin roles ni permisos diferenciados.
- La creación inicial de la cuenta podrá resolverse mediante configuración o carga administrativa, sin una interfaz para gestionar usuarios.

### 7.2. Pacientes

descrito a detalle en [features/atutenticacion](features/autenticacion.md)

- Registro de los datos mínimos necesarios para identificar al paciente y enviarle recordatorios.
- Consulta del listado y detalle de pacientes.
- Edición básica de sus datos.
- Registro de la confirmación de consentimiento para recibir mensajes por WhatsApp.

### 7.3. Biblioteca de ejercicios

descrito a detalle en [features/atutenticacion](features/autenticacion.md)

- Crear, listar, editar y seleccionar ejercicios.
- Cada ejercicio tendrá como mínimo nombre, descripción opcional y enlace de YouTube.
- No se almacenarán imágenes, videos, documentos ni otros archivos propios en el MVP.

### 7.4. Planes y rutinas

descrito a detalle en [features/atutenticacion](features/autenticacion.md)

- Crear un plan de ejercicios asociado a un paciente.
- Incluir una o más rutinas dentro del plan de modo que las rutinas sean contiguas en la fecha limite del plan de ejercicios.
- Agregar uno o más ejercicios de la biblioteca a cada rutina.
- Definir indicaciones básicas por ejercicio, como orden, series, repeticiones o duración cuando corresponda.
- Editar la composición y las indicaciones del plan y sus rutinas.

### 7.5. Página de rutina

descrito a detalle en [features/atutenticacion](features/autenticacion.md)

- Generar un enlace seguro asociado al plan del paciente.
- Permitir que el paciente consulte la rutina sin crear una cuenta ni iniciar sesión.
- Mostrar únicamente la información necesaria para realizar los ejercicios.
- Rechazar enlaces inválidos, vencidos o revocados.

### 7.6. Recordatorios

descrito a detalle en [features/atutenticacion](features/autenticacion.md)

- Programar uno o varios horarios diarios para un plan.
- Activar, editar o desactivar la programación.
- Ejecutar automáticamente los recordatorios en la zona horaria configurada para la clínica.

### 7.7. WhatsApp y registro técnico

descrito a detalle en [features/atutenticacion](features/autenticacion.md)

- Enviar mediante WhatsApp Cloud API un mensaje basado en una plantilla aprobada que incluya el enlace a la rutina.
- Guardar por cada intento la fecha y hora, el paciente, el recordatorio relacionado, el identificador devuelto por WhatsApp y un estado técnico básico.
- Distinguir como mínimo entre envío aceptado/exitoso y envío fallido.
- Guardar el código o detalle técnico del error cuando esté disponible.

## 8. Fuera del alcance del MVP

Las siguientes capacidades se aplazan deliberadamente:

- roles de administrador, gestión de especialistas y permisos;
- recuperación de contraseña por correo e interfaz de gestión de cuentas;
- configuración de la organización desde la aplicación;
- carga y almacenamiento de imágenes, videos, PDF u otros archivos;
- categorías, filtros avanzados y plantillas reutilizables de rutinas;
- respuestas del paciente por WhatsApp, botones interactivos y mensajes libres;
- registro de adherencia, cumplimiento o dolor;
- alertas clínicas o técnicas y su gestión;
- paneles, métricas, gráficas, reportes y exportaciones;
- historias clínicas, evoluciones, citas o teleconsultas;
- reintentos automáticos complejos;
- seguimiento detallado de estados entregado y leído;
- panel de auditoría y configuración de WhatsApp desde la interfaz;
- soporte para varias clínicas, varias organizaciones o múltiples especialistas.

Que una capacidad esté fuera del MVP no implica que se elimine definitivamente; deberá justificarse y priorizarse antes de incorporarla en una fase posterior.

## 9. Flujo principal del producto

1. El especialista inicia sesión.
2. Registra o selecciona un paciente.
3. Registra ejercicios con sus enlaces de YouTube o selecciona ejercicios existentes.
4. Crea un plan, agrega sus rutinas y organiza los ejercicios de cada una.
5. Programa uno o varios recordatorios diarios.
6. Al llegar un horario programado, el sistema genera o selecciona un enlace seguro y envía el recordatorio mediante WhatsApp.
7. El paciente abre el enlace y consulta su rutina.
8. El sistema registra si WhatsApp aceptó el envío o si ocurrió un error.
9. El especialista puede consultar ese resultado técnico.

## 10. Principios de producto

- **Simplicidad antes que amplitud:** toda función debe apoyar directamente el flujo principal del MVP.
- **Uso rápido:** las tareas frecuentes deben requerir pocos pasos y funcionar tanto en laptop como en celular.
- **Paciente sin fricción:** consultar una rutina no debe requerir una cuenta.
- **Privacidad desde el diseño:** se recopilarán únicamente los datos necesarios y los enlaces no expondrán identificadores predecibles ni datos sensibles.
- **Fallo visible:** un problema de envío no debe pasar inadvertido; debe quedar registrado para su consulta.
- **Mantenibilidad:** el alcance y las decisiones técnicas deben ser razonables para un proyecto desarrollado y mantenido por una sola persona.
- **Documentación como fuente de verdad:** alcance, reglas y decisiones se mantendrán en archivos Markdown versionados junto con el código.

## 11. Criterios de éxito iniciales

El MVP se considerará funcional cuando, en un entorno real de prueba de Sonqo Maki:

- el especialista pueda completar el flujo principal sin intervención técnica;
- un paciente pueda abrir desde su celular el enlace recibido y entender su rutina;
- los recordatorios se ejecuten en los horarios programados;
- cada intento de envío deje un resultado técnico consultable;
- los fallos no provoquen pérdida silenciosa de información ni envíos duplicados por la misma ejecución; y
- el sistema pueda operar inicialmente con aproximadamente 10 pacientes y dos recordatorios diarios por paciente, sin diseñar prematuramente para una escala mayor.

## 12. Restricciones y supuestos

- El MVP será exclusivo para la clínica Sonqo Maki.
- Existirá una sola cuenta operativa del especialista.
- El paciente debe haber autorizado previamente la recepción de mensajes por WhatsApp.
- Los videos serán proporcionados mediante enlaces de YouTube; Sonqo Maki no controla su disponibilidad futura.
- El envío depende de la disponibilidad, credenciales, políticas, plantillas aprobadas y costos de WhatsApp Cloud API.
- Un resultado aceptado por la API confirma la recepción técnica de la solicitud, no que el paciente haya leído o realizado la rutina.
- La duración, renovación y revocación exactas del enlace seguro se definirán como una regla de negocio específica antes de implementar esta función.

## 13. Decisiones pendientes

Estas decisiones deberán resolverse durante la depuración de requisitos, sin ampliar el alcance definido:

- datos mínimos obligatorios del paciente;
- ciclo de vida mínimo de pacientes, planes y rutinas;
- duración y política de renovación de los enlaces seguros;
- número máximo de recordatorios diarios y horarios permitidos;
- estado exacto que se mostrará como envío exitoso;
- necesidad de un reintento técnico simple ante errores temporales; y
- datos que el especialista podrá ver en el historial técnico de envíos.

## 14. Regla para aceptar nuevas funciones

Una función solo ingresará al MVP si es indispensable para completar, proteger o verificar el flujo principal descrito en este documento. Si puede realizarse manualmente durante la validación inicial, o si su valor depende de contar primero con respuestas, métricas, múltiples usuarios o mayor escala, se pospondrá.
