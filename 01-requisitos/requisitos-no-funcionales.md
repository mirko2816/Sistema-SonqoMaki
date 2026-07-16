# Requisitos no funcionales - Sonqo Maki

> Estado: depurado para MVP  
> Fecha: 13 de julio de 2026  
> Fuentes: `vision-del-producto.md`, `alcance-mvp.md`, `fuera-del-alcance-MVP.md` y PDF "Requisitos Funcionales y No Funcionales - antiguo.pdf"

## 1. Criterio de depuracion

Estos requisitos no funcionales se ajustan al MVP: una aplicacion web responsive, inicialmente local, operada por un especialista, con alrededor de 10 a 20 pacientes y hasta dos recordatorios diarios por plan de ejercicios.

Se reducen o posponen metas pensadas para una plataforma productiva amplia, como disponibilidad mensual del 99%, soporte de cientos de pacientes, auditoria avanzada, alertas, reportes y configuracion administrativa desde la interfaz.

## 2. Seguridad

### RNF-SEG-001 Almacenamiento seguro de contrasenas

Las contrasenas del especialista deben almacenarse usando hashing seguro, preferentemente bcrypt con costo minimo 12 o un mecanismo equivalente aceptado por el stack tecnologico elegido.

### RNF-SEG-002 Proteccion de sesiones

El sistema debe proteger las sesiones del especialista contra acceso no autorizado.

Las sesiones deben invalidarse al cerrar sesion.

### RNF-SEG-003 Comunicaciones seguras en despliegue

Cuando la aplicacion se ejecute en un entorno desplegado, toda comunicacion entre cliente y servidor debe usar HTTPS.

La primera ejecucion local del MVP puede operar sin HTTPS si se limita al entorno de desarrollo.

### RNF-SEG-004 Enlaces publicos no predecibles

Los enlaces publicos de rutina deben usar identificadores seguros, no secuenciales y no predecibles.

### RNF-SEG-005 Proteccion ante enlaces invalidos

Los enlaces invalidos, revocados o sin plan util no deben exponer datos personales ni detalles internos del sistema.

### RNF-SEG-006 Credenciales de WhatsApp

Las credenciales de WhatsApp Cloud API no deben quedar expuestas en el codigo fuente ni en archivos versionados.

Deben gestionarse mediante configuracion segura del entorno.

## 3. Privacidad y datos personales

### RNF-PRI-001 Minimizacion de datos

El sistema debe recopilar solo los datos necesarios para operar el flujo del MVP.

### RNF-PRI-002 Datos visibles en pagina publica

La pagina publica de rutina no debe mostrar datos personales sensibles del paciente.

### RNF-PRI-003 Consentimiento

El sistema debe conservar evidencia basica de la fecha de consentimiento WhatsApp del paciente antes de enviar recordatorios.

### RNF-PRI-004 No compartir datos con terceros no necesarios

Los datos del paciente no deben compartirse con terceros distintos de los servicios necesarios para operar el MVP, como WhatsApp Cloud API.

### RNF-PRI-005 Retencion de datos

La politica final de retencion y purga de datos queda pendiente de definicion antes de un despliegue productivo.

Para el MVP local, los pacientes se archivan mediante eliminacion logica. Sus datos permanecen reservados mientras sean recuperables y el historial tecnico minimo de recordatorios se conserva.

## 4. Rendimiento y capacidad

### RNF-REN-001 Capacidad inicial

El sistema debe operar inicialmente con aproximadamente 10 a 20 pacientes.

### RNF-REN-002 Carga esperada de recordatorios

El sistema debe soportar hasta dos recordatorios diarios por plan de ejercicios para la capacidad inicial del MVP.

### RNF-REN-003 Tiempo de respuesta de pantallas frecuentes

Las pantallas principales del especialista deben responder de forma fluida para el volumen inicial del MVP.

Como referencia, las consultas habituales no deberian superar 3 segundos en un entorno local o de prueba razonable.

### RNF-REN-004 Pagina publica ligera

La pagina publica de rutina debe cargar rapidamente en celular y evitar contenido pesado propio, ya que los materiales se enlazan como recursos externos.

## 5. Disponibilidad y operacion

### RNF-OPE-001 Operacion local inicial

La primera version del MVP debe poder ejecutarse localmente.

El despliegue en servidor contratado queda para una fase posterior.

### RNF-OPE-002 Dependencia de WhatsApp

El envio de recordatorios depende de la disponibilidad, credenciales, politicas, plantillas aprobadas y costos de WhatsApp Cloud API.

El sistema debe registrar los fallos cuando la API no acepte un envio.

### RNF-OPE-003 Sin perdida silenciosa de fallos

Un fallo de envio no debe pasar inadvertido. Debe quedar registrado en el historial tecnico del sistema.

### RNF-OPE-004 Sin duplicados por ejecucion

El sistema debe evitar que una misma ejecucion programada genere envios duplicados.

### RNF-OPE-005 Zona horaria

Los recordatorios deben calcularse usando la zona horaria fija del MVP: `America/Lima`.

## 6. Usabilidad y accesibilidad basica

### RNF-USA-001 Aplicacion responsive

La aplicacion debe ser responsive y usable en computadora, tablet y celular.

### RNF-USA-002 Uso frecuente en laptop y celular

Las tareas frecuentes del especialista deben poder realizarse tanto desde laptop como desde celular sin pasos innecesarios.

### RNF-USA-003 Pagina publica clara para paciente

La pagina publica debe ser comprensible para un paciente desde celular, mostrando la rutina vigente y las indicaciones sin requerir inicio de sesion.

### RNF-USA-004 Edicion comprensible de rutinas

La edicion de planes, rutinas y ejercicios debe ser clara para el especialista y reducir la posibilidad de confundir fechas, orden de ejercicios o horarios de recordatorio.

### RNF-USA-005 Estados vacios o no disponibles

Los enlaces enviados no deben dirigir a paginas vacias. Cuando no exista rutina vigente o el plan no este activo, la pagina debe mostrar un estado claro.

## 7. Mantenibilidad

### RNF-MAN-001 Alcance mantenible

La solucion debe mantenerse dentro de un alcance razonable para ser desarrollada y mantenida por una sola persona durante el MVP.

### RNF-MAN-002 Documentacion versionada

Las decisiones de producto, alcance, reglas y requisitos deben mantenerse en archivos Markdown versionados junto con el proyecto.

### RNF-MAN-003 Stack tecnologico aprobado

El MVP usara Laravel, Blade, Alpine.js para interacciones puntuales, Tailwind CSS y PostgreSQL. La aplicacion mantendra una arquitectura de monolito modular renderizado por el servidor.

### RNF-MAN-004 Configuracion separada del codigo

Los parametros sensibles o variables por entorno, como credenciales de WhatsApp, deben separarse del codigo fuente.

## 8. Integridad de datos

### RNF-DAT-001 Consistencia de planes y rutinas

El sistema debe preservar la consistencia entre plan, rutinas, ejercicios, recordatorios y pagina publica.

### RNF-DAT-002 Validacion de fechas

El sistema debe impedir rutinas superpuestas, fuera del rango del plan o no contiguas dentro del mismo plan.

### RNF-DAT-003 Historial tecnico minimo

El sistema debe conservar el historial tecnico basico de ejecuciones omitidas, solicitudes aceptadas y solicitudes fallidas.

### RNF-DAT-004 Datos necesarios para operar recordatorios

El sistema debe validar que existan paciente activo, consentimiento, telefono valido, plan activo, rutina vigente y recordatorio activo antes de intentar enviar un mensaje.

## 9. Requisitos no funcionales pospuestos o ajustados

Los siguientes requisitos se posponen o ajustan para el MVP:

- disponibilidad mensual de 99%;
- ventana formal de mantenimiento productivo;
- soporte para 200 pacientes activos;
- soporte para 500 recordatorios diarios;
- limite de tres recordatorios diarios por paciente;
- limite operativo de 100 clientes diarios por WhatsApp;
- archivos multimedia de hasta 16 MB;
- cifrado especifico de reportes de dolor o historiales clinicos, porque esos modulos no forman parte del MVP;
- logs de auditoria inmutables y consultables por administrador;
- conservacion obligatoria de logs por 2 anos;
- alertas por token de WhatsApp proximo a expirar;
- politica de privacidad final y periodo de retencion de 3 anos, pendientes para despliegue productivo.
