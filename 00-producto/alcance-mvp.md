# Alcance del MVP - Sonqo Maki

> Estado: aprobado para implementacion
> Fecha: 13 de julio de 2026  
> Relacionado con: [vision-del-producto.md](vision-del-producto.md)

## 1. Proposito del MVP

El MVP de Sonqo Maki permitira que especialistas registren pacientes, creen planes de ejercicios con rutinas contiguas, configuren ejercicios, programen recordatorios por WhatsApp y compartan automaticamente un enlace seguro para que el paciente consulte solo la rutina vigente del dia.

El alcance se concentra en validar el flujo principal de atencion domiciliaria, evitando funciones administrativas, clinicas o analiticas que no sean necesarias para operar la primera version.

## 2. Flujo principal incluido

1. El especialista inicia sesion.
2. Registra o actualiza pacientes.
3. Crea ejercicios reutilizables.
4. Crea un plan de ejercicios para un paciente.
5. Define una o mas rutinas dentro del plan.
6. Ordena y configura los ejercicios de cada rutina.
7. Programa hasta dos recordatorios diarios para ciertos dias de la semana.
8. El sistema envia recordatorios por WhatsApp cuando corresponde.
9. El paciente abre el enlace recibido y ve la rutina vigente del dia.
10. El sistema registra si WhatsApp acepto o fallo el envio.

## 3. Usuarios incluidos

### Especialista

Usuario autenticado del sistema. Puede registrar pacientes, editar pacientes, gestionar ejercicios, crear planes y rutinas, programar recordatorios y consultar el estado basico de los envios.

Las cuentas de especialistas seran creadas manualmente durante el MVP. El inicio de sesion sera con email y contrasena. No se incluye recuperacion de contrasena.

### Paciente

Actor sin cuenta. Recibe recordatorios por WhatsApp y accede mediante un enlace seguro a la rutina vigente del dia. No inicia sesion, no edita informacion y no visualiza rutinas pasadas ni futuras.

## 4. Modulos incluidos

### 4.1. Autenticacion

Incluye inicio y cierre de sesion para especialistas con email y contrasena.

No incluye registro publico de usuarios, gestion de cuentas desde la interfaz ni recuperacion de contrasena.

### 4.2. Pacientes

El sistema permitira registrar, editar, listar y archivar pacientes mediante eliminacion logica.

Datos incluidos del paciente:

- nombres;
- apellidos;
- telefono con formato peruano `+51`;
- DNI, opcional;
- fecha de consentimiento para recibir mensajes.

El paciente podra estar en estado activo o inactivo.

### 4.3. Ejercicios

El sistema permitira crear, editar, listar y reutilizar ejercicios.

Datos incluidos del ejercicio:

- nombre, obligatorio;
- descripcion, opcional;
- duracion, opcional;
- sets, opcional;
- repeticiones, opcional;
- URL de material, opcional.

La URL de material podra apuntar a video, imagen u otro recurso externo. El MVP no almacenara videos, imagenes ni archivos propios en la base de datos.

YouTube sera el caso habitual, pero no el unico proveedor admitido.

Un ejercicio podra reutilizarse en varias rutinas. Dentro de cada rutina, los ejercicios deberan poder ordenarse manualmente.

El sistema incluira una biblioteca de rutinas reutilizables. Al usar una rutina de biblioteca en un plan, se creara una copia editable e independiente con sus ejercicios; los cambios posteriores no modificaran la plantilla original.

### 4.4. Planes de ejercicios

Un plan de ejercicios sera el contenedor logico que relaciona a un paciente con una o mas rutinas dentro de un rango definido de fechas.

Datos incluidos del plan:

- paciente al que pertenece;
- nombre;
- fecha de inicio;
- fecha de fin;
- estado.

Un paciente podra tener distintos planes de ejercicios.

Estados considerados para el MVP:

- activo;
- en pausa;
- finalizado.

### 4.5. Rutinas

Una rutina pertenece a un plan de ejercicios. Un plan podra tener una o mas rutinas.

Datos incluidos de la rutina:

- plan al que pertenece;
- nombre;
- fecha de inicio;
- fecha de fin.

Las rutinas de un mismo plan no deben interferirse en el tiempo. Deben ser contiguas y no exceder la fecha de inicio ni la fecha de fin del plan.

Cuando una rutina termine, el sistema debera usar automaticamente la siguiente rutina vigente dentro del plan.

Por cada ejercicio agregado a una rutina, el especialista podra configurar:

- sets;
- repeticiones;
- duracion;
- URL de material.

### 4.6. Pagina publica de rutina

El enlace publico sera generado automaticamente por el servidor cuando el plan se active por primera vez. Cada enlace pertenecera exclusivamente a un plan y nunca podra reutilizarse para identificar otro.

El especialista no administrara enlaces desde la interfaz. Solo una operacion tecnica autorizada podra revocarlos o rotarlos por seguridad.

Cuando el plan este activo, el enlace mostrara solo la rutina vigente del dia.

La pagina publica no mostrara informacion personal del paciente. Solo mostrara el nombre de la rutina y la informacion necesaria para realizar los ejercicios.

El paciente no podra ver rutinas futuras ni rutinas pasadas.

El enlace no vence mientras el plan de ejercicios este activo. Si el estado del plan cambia, el enlace seguira existiendo, pero mostrara una pagina estatica segun corresponda:

- plan finalizado: mensaje indicando que el paciente concluyo su plan de ejercicios;
- plan en pausa: mensaje indicando que el plan esta en pausa y que debe comunicarse con su especialista;

### 4.7. Recordatorios

Los recordatorios se configuraran por plan de ejercicios.

Cada plan podra tener como maximo dos recordatorios por dia.

Los recordatorios se enviaran solo ciertos dias de la semana, definidos por el especialista.

El especialista podra pausar los recordatorios de un plan.

Si el plan esta en pausa o finalizado, el sistema dejara de enviar recordatorios.

### 4.8. WhatsApp

El envio de recordatorios se realizara mediante WhatsApp Cloud API.

Durante el MVP no existe aun una plantilla aprobada. El texto base esperado del recordatorio es:

```text
Hola {nombre}. Tu salud es importante. recuerda realizar tu rutina de hoy: {enlace}.
```

El sistema registrara cada ejecucion programada como omitida, aceptada o fallida. Los estados posteriores de entrega o lectura y la recepcion de webhooks quedan fuera del MVP.

No se incluyen reintentos automaticos. Si un envio falla, el sistema solo registrara el fallo.

### 4.9. Pantallas minimas

El MVP incluira las siguientes pantallas:

- inicio de sesion;
- dashboard simple;
- pacientes;
- detalle de paciente;
- biblioteca de ejercicios;
- biblioteca de rutinas;
- crear y editar plan;
- configurar rutinas;
- configurar recordatorios;
- historial basico de envios;
- pagina publica de rutina.

El dashboard mostrara como minimo:

- nombre del paciente;
- telefono;
- estado del plan;
- estado de recordatorios.

## 5. Restricciones del MVP

- La aplicacion sera web responsive.
- La primera ejecucion sera local y permitira comprobar recordatorios reales mientras la aplicacion, el programador de tareas y la conexion a Internet permanezcan activos.
- El despliegue en servidor contratado se realizara en una fase posterior.
- El stack aprobado es Laravel, Blade, Alpine.js para interacciones puntuales, Tailwind CSS y PostgreSQL.
- El MVP debe operar inicialmente con aproximadamente 10 a 20 pacientes.
- El sistema no debe generar spam de mensajes.
- Los enlaces enviados no deben dirigir a paginas vacias o sin estado claro.
- La edicion de rutinas debe ser comprensible para el especialista.

## 6. Fuera del alcance inmediato

Quedan fuera del MVP:

- recuperacion de contrasena;
- registro publico de usuarios;
- gestion avanzada de especialistas;
- roles y permisos diferenciados;
- carga o almacenamiento propio de imagenes, videos o archivos;
- visualizacion de rutinas pasadas o futuras por parte del paciente;
- respuestas del paciente por WhatsApp;
- reintentos automaticos de envio;
- metricas, reportes o graficas;
- seguimiento de adherencia, dolor o cumplimiento;
- historias clinicas, citas o teleconsultas;
- despliegue productivo en servidor contratado;
- recepcion de webhooks y seguimiento posterior de estados entregado o leido.

## 7. Criterio de finalizacion del MVP

El MVP se considerara funcional cuando el especialista pueda iniciar sesion, registrar pacientes, crear planes con rutinas y ejercicios, programar recordatorios, enviar mensajes por WhatsApp y permitir que el paciente acceda desde la URL recibida a la rutina vigente del dia.

La primera version debera evitar especialmente:

- envios duplicados o spam;
- enlaces sin destino util;
- confusion al editar planes de ejercicios y rutinas;
- perdida del estado basico de los envios.
