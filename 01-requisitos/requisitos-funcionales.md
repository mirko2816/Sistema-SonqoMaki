# Requisitos funcionales - Sonqo Maki

> Estado: depurado para MVP  
> Fecha: 13 de julio de 2026  
> Fuentes: `vision-del-producto.md`, `alcance-mvp.md`, `fuera-del-alcance-MVP.md` y PDF "Requisitos Funcionales y No Funcionales - antiguo.pdf"

## 1. Criterio de depuracion

Estos requisitos conservan solo las capacidades necesarias para validar el flujo principal del MVP: registrar pacientes, crear ejercicios, armar planes con rutinas, programar recordatorios por WhatsApp, mostrar al paciente la rutina vigente mediante un enlace seguro y registrar el resultado tecnico de cada envio.

Quedan fuera los modulos de administracion avanzada, multiples roles, recuperacion de contrasena, almacenamiento propio de archivos, respuestas del paciente por WhatsApp, alertas clinicas, metricas, reportes, exportaciones, auditoria avanzada y reintentos automaticos.

## 2. Actores

### RF-ACT-001 Especialista autenticado

El sistema debe permitir que el especialista use la aplicacion web mediante una cuenta creada manualmente para el MVP.

El especialista puede:

- iniciar y cerrar sesion;
- registrar, consultar, editar, activar, inactivar o archivar pacientes;
- gestionar ejercicios reutilizables;
- crear y editar planes de ejercicios;
- crear y ordenar rutinas dentro de un plan;
- configurar recordatorios del plan;
- consultar el historial basico de envios por WhatsApp.

### RF-ACT-002 Paciente sin cuenta

El paciente no debe iniciar sesion ni administrar informacion dentro del sistema.

El paciente solo puede acceder, mediante un enlace seguro recibido por WhatsApp, a la pagina publica de la rutina vigente del dia.

### RF-ACT-003 WhatsApp Cloud API

El sistema debe usar WhatsApp Cloud API como canal externo para enviar recordatorios y registrar el resultado tecnico de cada intento.

## 3. Autenticacion

### RF-AUT-001 Inicio de sesion

El sistema debe permitir que el especialista inicie sesion con email y contrasena.

### RF-AUT-002 Cierre de sesion

El sistema debe permitir que el especialista cierre sesion manualmente.

### RF-AUT-003 Cuenta creada manualmente

La cuenta inicial del especialista debe poder crearse por configuracion o carga administrativa, sin una pantalla publica de registro.

### RF-AUT-004 Restriccion de roles

El MVP debe manejar un unico tipo de usuario autenticado: especialista. No debe incluir roles diferenciados de administrador, permisos avanzados ni gestion de multiples especialistas desde la interfaz.

## 4. Pacientes

### RF-PAC-001 Registro de paciente

El sistema debe permitir registrar pacientes con los datos minimos del MVP:

- nombres;
- apellidos;
- telefono WhatsApp con formato peruano `+51`;
- DNI, opcional;
- fecha de consentimiento para recibir mensajes por WhatsApp.

### RF-PAC-002 Validacion de datos del paciente

El sistema debe validar que el telefono tenga formato peruano `+51`, que el telefono normalizado no se repita entre pacientes activos, inactivos o archivados y que el DNI, cuando se proporcione, tenga ocho digitos y no se repita.

### RF-PAC-003 Listado de pacientes

El sistema debe mostrar un listado de pacientes activos e inactivos para que el especialista pueda encontrarlos y acceder a su detalle.

Los pacientes archivados (eliminación lógica) no aparecen en esta lista principal. El especialista puede acceder a ellos mediante una sección separada de pacientes archivados.

### RF-PAC-004 Detalle de paciente

El sistema debe mostrar el detalle basico del paciente, incluyendo sus datos de contacto, estado y planes asociados.

### RF-PAC-005 Edicion de paciente

El sistema debe permitir editar los datos basicos del paciente.

### RF-PAC-006 Estado del paciente

El sistema debe permitir marcar a un paciente como **activo o inactivo**.

Un paciente inactivo no debe recibir recordatorios.

### RF-PAC-007 Archivo de paciente

El sistema debe permitir archivar pacientes mediante eliminacion logica.

Antes de confirmar, el sistema debe advertir que el paciente dejara de aparecer en las consultas normales, sus planes quedaran pausados, sus recordatorios se desactivaran y sus enlaces publicos se revocaran.

El archivo debe requerir una confirmacion explicita del especialista.

### RF-PAC-008 Conservacion y restauracion del paciente archivado

Cuando se archive un paciente, el sistema debe conservar su informacion operativa para una posible restauracion tecnica:

- planes, rutinas y ejercicios configurados;
- horarios de recordatorio, que quedaran inactivos;
- enlaces publicos, que quedaran revocados;
- historial de ejecuciones de recordatorios.

El archivo no debe modificar ejercicios ni plantillas de rutina de las bibliotecas. La restauracion sera una operacion tecnica durante el MVP y no reactivara automaticamente planes, recordatorios ni enlaces.

### RF-PAC-009 Consentimiento WhatsApp

El sistema debe almacenar la fecha en que el especialista confirma que el paciente otorgo consentimiento para recibir mensajes por WhatsApp.

El sistema no debe enviar recordatorios a pacientes sin consentimiento registrado.

## 5. Ejercicios

### RF-EJE-001 Crear ejercicio

El sistema debe permitir crear ejercicios reutilizables.

Datos del ejercicio:

- nombre, obligatorio;
- descripcion, opcional;
- duracion, opcional;
- sets, opcional;
- repeticiones, opcional;
- URL de material externo, opcional.

### RF-EJE-002 Listar ejercicios

El sistema debe permitir listar los ejercicios registrados en la biblioteca.

### RF-EJE-003 Editar ejercicio

El sistema debe permitir editar los datos de un ejercicio.

### RF-EJE-004 Reutilizar ejercicio

El sistema debe permitir reutilizar un ejercicio en distintas rutinas.

### RF-EJE-005 Material externo

La URL de material puede apuntar a YouTube u otro recurso externo. El MVP no debe cargar ni almacenar videos, imagenes, PDF ni archivos propios.

### RF-EJE-006 Biblioteca de ejercicios

El sistema debe mantener una biblioteca de ejercicios independiente de los pacientes y planes.

Los ejercicios guardados en esta biblioteca deben poder seleccionarse al configurar rutinas y no deben modificarse cuando se archive un paciente.

### RF-EJE-007 Eliminar ejercicio de la biblioteca

El sistema debe permitir al especialista eliminar un ejercicio de la biblioteca mediante eliminación lógica.

Cuando se elimine un ejercicio:

- desaparece del listado de la biblioteca y no puede seleccionarse para nuevas rutinas;
- las copias ya configuradas dentro de rutinas de planes existentes (`routine_exercises`) no se modifican ni eliminan, porque son copias independientes;
- la referencia `source_exercise_id` de esas copias queda nula si el ejercicio se purga físicamente en el futuro, pero no afecta su funcionamiento operativo.

El sistema debe pedir confirmación explícita antes de eliminar, indicando que el ejercicio dejará de estar disponible para nuevas rutinas.

## 6. Planes de ejercicios

### RF-PLA-001 Crear plan

El sistema debe permitir crear un plan de ejercicios asociado a un paciente.

Datos del plan:

- paciente;
- nombre;
- fecha de inicio;
- fecha de fin;
- estado.

### RF-PLA-002 Estados del plan

El sistema debe manejar los siguientes estados del plan:

- activo;
- en pausa;
- finalizado.

### RF-PLA-003 Editar plan

El sistema debe permitir editar los datos basicos de un plan y su composicion mientras no rompa las reglas de fechas de sus rutinas.

### RF-PLA-004 Multiples planes por paciente

El sistema debe permitir que un paciente tenga distintos planes de ejercicios.

Cada plan debe gestionar sus propias rutinas, recordatorios y enlace publico.

### RF-PLA-005 Finalizacion del plan

Cuando el plan llegue a su fecha de fin, el sistema debe dejar de enviar recordatorios asociados a ese plan.

Una tarea programada diaria debe actualizar de forma persistente el campo `status` de `active` a `finished` para todo plan cuya `ends_on` sea anterior a la fecha actual en `America/Lima`. Esto garantiza que el dashboard y el historial reflejen siempre el estado real sin depender de cálculos en tiempo de ejecución.

Además, la lógica de envío de recordatorios trata como finalizado cualquier plan cuya fecha actual supere `ends_on`, aunque el scheduler aún no haya actualizado el campo persistido.

Un plan finalizado no debe poder reactivarse ni volver a pausa. Para reutilizarlo debe duplicarse como un plan nuevo.

### RF-PLA-006 Duplicacion de plan

El sistema debe permitir duplicar un plan de ejercicios existente y asignarlo a cualquier paciente, incluido el mismo paciente del plan original.

Al duplicar el plan, el sistema debe crear una copia editable del plan, sus rutinas y la configuracion de ejercicios, sin vincular la nueva copia al plan de origen.

El especialista debe poder ajustar fechas, rutinas, ejercicios, recordatorios y estado del nuevo plan antes de usarlo.

## 7. Rutinas

### RF-RUT-001 Crear rutina

El sistema debe permitir crear una o mas rutinas dentro de un plan.

Datos de la rutina:

- plan al que pertenece;
- nombre;
- fecha de inicio;
- fecha de fin.

### RF-RUT-002 Reglas de fechas de rutinas

Las rutinas de un mismo plan deben:

- estar dentro de la fecha de inicio y fecha de fin del plan;
- no superponerse entre si;
- ser contiguas cuando exista mas de una rutina en el plan.

### RF-RUT-003 Rutina vigente

El sistema debe identificar automaticamente la rutina vigente del dia dentro de un plan activo.

### RF-RUT-004 Agregar ejercicios a rutina

El sistema debe permitir agregar uno o mas ejercicios de la biblioteca a una rutina.

### RF-RUT-005 Ordenar ejercicios

El sistema debe permitir ordenar manualmente los ejercicios dentro de una rutina.

### RF-RUT-006 Configurar ejercicio dentro de rutina

Por cada ejercicio agregado a una rutina, el especialista debe poder ajustar:

- sets;
- repeticiones;
- duracion;
- URL de material.

### RF-RUT-007 Validacion minima para activar uso del plan

Un plan debe tener al menos una rutina y cada rutina debe tener al menos un ejercicio para ser util en la pagina publica y en los recordatorios.

### RF-RUT-008 Biblioteca de rutinas

El sistema debe permitir crear, consultar, editar y archivar rutinas reutilizables en una biblioteca independiente de los pacientes.

Una rutina de biblioteca debe poder usarse como base para crear una rutina dentro de un plan de paciente.

Al usar una rutina de biblioteca en un plan, el sistema debe crear una copia editable para ese plan, de modo que los cambios del paciente no modifiquen la rutina original de la biblioteca.

Las rutinas guardadas en la biblioteca no deben modificarse cuando se archive un paciente.

**Archivado de plantillas de rutina:**

El especialista puede archivar una plantilla de rutina. Al hacerlo:

- la plantilla cambia su estado a `archived`;
- deja de aparecer en el listado principal de la biblioteca y no puede seleccionarse para nuevos planes;
- pasa a una sección separada de archivados, accesible desde la biblioteca;
- las copias ya generadas en planes de pacientes no se modifican ni eliminan;
- el especialista puede restaurar la plantilla a estado `active` desde la sección de archivados si lo necesita.


## 8. Pagina publica de rutina

### RF-PUB-001 Enlace seguro

El sistema debe generar automaticamente un enlace seguro al activar el plan por primera vez.

El especialista no debe administrar manualmente los enlaces.

Cada enlace debe pertenecer exclusivamente a un plan y no puede reutilizarse para identificar otro. Solo una operacion tecnica autorizada puede revocarlo o rotarlo por seguridad.

### RF-PUB-002 Acceso sin cuenta

La pagina publica debe permitir que el paciente consulte su rutina sin crear cuenta ni iniciar sesion.

### RF-PUB-003 Contenido visible para el paciente

La pagina publica debe mostrar solo la informacion necesaria para realizar la rutina vigente:

- nombre de la rutina;
- ejercicios;
- indicaciones configuradas por ejercicio;
- enlaces externos de material cuando existan.

No debe mostrar informacion personal sensible del paciente.

> **Nota UX:** El nombre de la rutina definido por el especialista es visible directamente para el paciente en esta página. La interfaz de configuración debe dejar claro que ese campo es público, para que el especialista elija nombres comprensibles para el paciente (por ejemplo: "Semana 1 — Movilidad de hombro") y no nombres de uso interno.

### RF-PUB-004 Restriccion de rutinas pasadas y futuras

La pagina publica no debe permitir que el paciente vea rutinas pasadas ni futuras.

### RF-PUB-005 Plan en pausa

Si el plan esta en pausa, el enlace debe mostrar una pagina estatica indicando que el plan esta en pausa y que el paciente debe comunicarse con su especialista.

### RF-PUB-006 Plan finalizado

Si el plan esta finalizado, el enlace debe mostrar una pagina estatica indicando que el paciente concluyo su plan de ejercicios.

### RF-PUB-007 Enlaces invalidos

El sistema debe rechazar enlaces invalidos o revocados mostrando un estado claro, sin exponer datos del paciente.

### RF-PUB-008 Tratamiento de enlaces antiguos

Los enlaces publicos generados previamente deben continuar asociados al plan, no a una rutina especifica. Cada vez que el paciente abra uno de estos enlaces, el sistema debe localizar el plan y evaluar su estado y sus fechas usando el dia calendario de `America/Lima`.

El resultado debe corresponder al estado actual del plan:

- si esta en pausa, debe mostrar una pagina estatica con el mensaje: "Su plan de ejercicios se encuentra pausado. Comuniquese con el especialista encargado";
- si esta finalizado o la fecha actual es posterior a su fecha de fin, debe mostrar una pagina estatica con el mensaje: "Plan de ejercicios finalizado. Para mas consultas, comuniquese con el especialista encargado";
- si esta activo, la fecha actual esta dentro de su rango y existe exactamente una rutina vigente, debe dirigir a la pagina publica de esa rutina.

Un enlace antiguo no debe conservar ni exponer una rutina que dejo de estar vigente. Si el enlace es invalido o revocado, la fecha actual es anterior al inicio del plan o no existe exactamente una rutina vigente, el sistema debe mostrar un estado generico no disponible sin exponer datos personales, rutinas pasadas ni rutinas futuras.

## 9. Recordatorios

### RF-REC-001 Configurar recordatorios por plan

El sistema debe permitir configurar recordatorios asociados a un plan de ejercicios.

### RF-REC-002 Limite de recordatorios diarios

Cada plan puede tener como maximo dos recordatorios por dia.

### RF-REC-003 Dias de envio

El especialista debe poder definir en que dias de la semana se enviaran los recordatorios.

### RF-REC-004 Horarios de envio

El especialista debe poder definir los horarios de envio de los recordatorios.

Los horarios deben ejecutarse en la zona horaria fija del MVP: `America/Lima`.

### RF-REC-005 Pausar recordatorios

El especialista debe poder pausar los recordatorios de un plan.

### RF-REC-006 Condiciones para enviar recordatorios

El sistema solo debe enviar recordatorios cuando:

- el paciente este activo;
- el paciente tenga consentimiento WhatsApp registrado;
- el plan este activo;
- el plan tenga rutinas contiguas, sin huecos ni fechas dentro del rango del plan sin una rutina asociada;
- los recordatorios del plan esten activos;
- exista una rutina vigente para el dia;
- el enlace publico tenga un destino util.

### RF-REC-007 Validacion del plan antes del envio

Antes de enviar recordatorios de un plan, el sistema debe validar que todo el rango de fechas del plan este cubierto por rutinas asociadas.

El sistema no debe enviar recordatorios si el plan tiene huecos de fechas, rutinas superpuestas o dias dentro del rango del plan sin una rutina asociada.

Cuando la validacion falle, el sistema debe registrar o mostrar un estado claro para que el especialista pueda corregir la configuracion del plan.

### RF-REC-008 Detener envios por estado del plan

Si el plan esta en pausa o finalizado, el sistema no debe enviar recordatorios asociados a ese plan.

### RF-REC-009 Evitar duplicados

El sistema debe evitar envios duplicados para la misma ejecucion programada de un recordatorio.

## 10. WhatsApp e historial tecnico

### RF-WPP-001 Envio de recordatorio

El sistema debe enviar recordatorios mediante WhatsApp Cloud API.

### RF-WPP-002 Contenido base del mensaje

Durante el MVP, el mensaje debe incluir un texto breve y el enlace seguro a la rutina vigente.

Texto base esperado:

```text
Hola {nombre}. Tu salud es importante. Recuerda realizar tu rutina de hoy: {enlace}.
```

El contenido final debe adaptarse a las reglas de plantillas aprobadas por Meta cuando corresponda.

### RF-WPP-003 Registro de ejecucion programada

El sistema debe registrar cada ejecucion programada, aunque se omita antes de llamar a WhatsApp:

- fecha y hora;
- paciente;
- plan;
- recordatorio relacionado;
- identificador devuelto por WhatsApp, si existe;
- estado tecnico basico;
- codigo o detalle tecnico del error, si existe.

### RF-WPP-004 Resultados tecnicos minimos

El sistema debe distinguir como minimo entre:

- omitido;
- aceptado;
- fallido.

`Aceptado` confirma solamente la respuesta inmediata de WhatsApp y no debe denominarse `exitoso`.

### RF-WPP-005 Sin reintentos automaticos

El MVP no debe ejecutar reintentos automaticos si un envio falla. El sistema solo debe registrar el fallo.

### RF-WPP-006 Historial basico de recordatorios

El especialista debe poder consultar un historial basico de ejecuciones de recordatorios, incluidas las omitidas, aceptadas y fallidas.

## 11. Dashboard y pantallas minimas

### RF-UI-001 Pantallas minimas del MVP

El sistema debe incluir como minimo las siguientes pantallas:

- inicio de sesion;
- dashboard simple;
- pacientes;
- detalle de paciente;
- biblioteca de ejercicios;
- biblioteca de rutinas;
- crear y editar plan;
- configurar rutinas;
- configurar recordatorios;
- historial basico de recordatorios;
- pagina publica de rutina.

### RF-UI-002 Dashboard simple

El dashboard debe mostrar una fila por plan activo. Si un paciente tiene varios planes activos simultáneos, cada plan ocupa una fila independiente.

Cada fila debe mostrar como mínimo:

- nombre del paciente;
- teléfono;
- nombre del plan;
- estado del plan;
- estado de recordatorios.

## 12. Requisitos funcionales pospuestos

Los siguientes requisitos del PDF quedan fuera del MVP por contradecir o exceder `alcance-mvp.md` y `fuera-del-alcance-MVP.md`:

- gestion de administradores, especialistas, roles y permisos;
- configuracion de organizacion desde la aplicacion;
- supervision global de uso, metricas y exportaciones;
- gestion de logs de auditoria desde interfaz;
- configuracion de credenciales y templates de WhatsApp desde interfaz;
- recuperacion de contrasena;
- desbloqueo manual de cuentas por administrador;
- multiples especialistas con visibilidad global;
- carga y almacenamiento propio de imagenes, videos, PDF o archivos;
- categorias y filtros avanzados de rutinas;
- respuestas estructuradas del paciente por WhatsApp;
- mensajes libres del paciente;
- registro de cumplimiento, dolor o solicitud de contacto;
- rerecordatorios por postergacion;
- alertas clinicas o tecnicas y gestion de alertas;
- notificaciones al especialista por correo;
- panel de seguimiento con graficas;
- reportes PDF o CSV;
- historial completo de mensajes enviados y recibidos;
- baja automatica del canal WhatsApp por respuestas entrantes;
- reintentos automaticos de envio.
- recepcion de webhooks y seguimiento de estados posteriores de entrega o lectura.
