# Features del especialista

> Actor: especialista autenticado  
> Alcance: MVP  
> Detalle de flujos y excepciones: casos de uso referenciados.

## Criterio documental

Este archivo funciona como catálogo de capacidades orientado al valor del actor. No repite precondiciones, pasos, validaciones ni postcondiciones ya definidos en los casos de uso y reglas de negocio.

## Acceso y vista operativa

### FE-ESP-AUT-001 - Iniciar sesión

**Como** especialista, **quiero** iniciar sesión con email y contraseña, **para** acceder de forma segura a mi espacio de trabajo.  
**Detalle:** [CU-AUT-001](../02-casos-de-uso/autenticacion/CU-AUT-001-iniciar-sesion.md)

### FE-ESP-AUT-002 - Cerrar sesión

**Como** especialista, **quiero** cerrar mi sesión, **para** proteger la información cuando termine de usar el sistema.  
**Detalle:** [CU-AUT-002](../02-casos-de-uso/autenticacion/CU-AUT-002-cerrar-sesion.md)

### FE-ESP-UI-001 - Consultar el dashboard

**Como** especialista, **quiero** ver el estado general de pacientes, planes y recordatorios, **para** acceder rápidamente al trabajo pendiente.  
**Detalle:** [CU-UI-001](../02-casos-de-uso/dashboard/CU-UI-001-consultar-dashboard.md)

## Pacientes

### FE-ESP-PAC-001 - Registrar un paciente

**Como** especialista, **quiero** registrar un paciente y su consentimiento, **para** asignarle planes y habilitar futuros recordatorios.  
**Detalle:** [CU-PAC-001](../02-casos-de-uso/pacientes/CU-PAC-001-registrar-paciente.md)

### FE-ESP-PAC-002 - Consultar pacientes

**Como** especialista, **quiero** encontrar pacientes y consultar su detalle, **para** revisar sus datos y planes asociados.  
**Detalle:** [CU-PAC-002](../02-casos-de-uso/pacientes/CU-PAC-002-consultar-pacientes.md)

### FE-ESP-PAC-003 - Editar un paciente

**Como** especialista, **quiero** actualizar los datos de un paciente, **para** mantener correcta su identificación y contacto.  
**Detalle:** [CU-PAC-003](../02-casos-de-uso/pacientes/CU-PAC-003-editar-paciente.md)

### FE-ESP-PAC-004 - Cambiar el estado de un paciente

**Como** especialista, **quiero** activar o inactivar un paciente, **para** controlar su participación en futuros envíos sin perder su información.  
**Detalle:** [CU-PAC-004](../02-casos-de-uso/pacientes/CU-PAC-004-cambiar-estado-paciente.md)

### FE-ESP-PAC-005 - Eliminar un paciente

**Como** especialista, **quiero** eliminar definitivamente un paciente con confirmación previa, **para** retirar su información operativa cuando corresponda.  
**Detalle:** [CU-PAC-005](../02-casos-de-uso/pacientes/CU-PAC-005-eliminar-paciente.md)

## Ejercicios

### FE-ESP-EJE-001 - Crear un ejercicio

**Como** especialista, **quiero** registrar ejercicios reutilizables, **para** formar una biblioteca propia.  
**Detalle:** [CU-EJE-001](../02-casos-de-uso/ejercicios/CU-EJE-001-crear-ejercicio.md)

### FE-ESP-EJE-002 - Consultar y seleccionar ejercicios

**Como** especialista, **quiero** consultar y seleccionar ejercicios de la biblioteca, **para** incorporarlos rápidamente a una rutina.  
**Detalle:** [CU-EJE-002](../02-casos-de-uso/ejercicios/CU-EJE-002-consultar-seleccionar-ejercicios.md)

### FE-ESP-EJE-003 - Editar un ejercicio

**Como** especialista, **quiero** actualizar un ejercicio de la biblioteca, **para** corregir o mejorar su información base sin alterar las copias ya configuradas.  
**Detalle:** [CU-EJE-003](../02-casos-de-uso/ejercicios/CU-EJE-003-editar-ejercicio.md)

## Planes y rutinas

### FE-ESP-PLA-001 - Crear un plan

**Como** especialista, **quiero** crear un plan para un paciente, **para** organizar sus ejercicios dentro de un periodo.  
**Detalle:** [CU-PLA-001](../02-casos-de-uso/planes/CU-PLA-001-crear-plan.md)

### FE-ESP-PLA-002 - Editar un plan

**Como** especialista, **quiero** modificar un plan, **para** adaptarlo sin romper la validez de sus rutinas.  
**Detalle:** [CU-PLA-002](../02-casos-de-uso/planes/CU-PLA-002-editar-plan.md)

### FE-ESP-PLA-003 - Duplicar un plan

**Como** especialista, **quiero** copiar un plan para otro paciente, **para** reutilizar su estructura como una configuración independiente.  
**Detalle:** [CU-PLA-003](../02-casos-de-uso/planes/CU-PLA-003-duplicar-plan.md)

### FE-ESP-PLA-004 - Controlar el estado de un plan

**Como** especialista, **quiero** activar, pausar, reactivar o finalizar un plan, **para** controlar su disponibilidad pública y sus envíos.  
**Detalle:** [CU-PLA-004](../02-casos-de-uso/planes/CU-PLA-004-cambiar-estado-plan.md)

### FE-ESP-RUT-001 - Configurar las rutinas de un plan

**Como** especialista, **quiero** definir rutinas contiguas y personalizar sus ejercicios, **para** cubrir cada etapa del plan con indicaciones ordenadas.  
**Detalle:** [CU-RUT-001](../02-casos-de-uso/rutinas/CU-RUT-001-configurar-rutinas.md)

## Recordatorios y envíos

### FE-ESP-REC-001 - Configurar recordatorios

**Como** especialista, **quiero** definir días, horarios y estado de los recordatorios de un plan, **para** automatizar avisos sin exceder los límites establecidos.  
**Detalle:** [CU-REC-001](../02-casos-de-uso/recordatorios/CU-REC-001-configurar-recordatorios.md)

### FE-ESP-REC-002 - Conocer impedimentos de envío

**Como** especialista, **quiero** conocer qué condición impide que un plan envíe recordatorios, **para** corregirla antes de una próxima ejecución.  
**Detalle:** condiciones de [CU-REC-002](../02-casos-de-uso/recordatorios/CU-REC-002-ejecutar-envio-programado.md)

### FE-ESP-ENV-001 - Consultar el historial técnico

**Como** especialista, **quiero** revisar los intentos de envío y sus errores, **para** distinguir solicitudes aceptadas de fallos técnicos.  
**Detalle:** [CU-ENV-001](../02-casos-de-uso/envios/CU-ENV-001-consultar-historial-envios.md)

## Límites del actor

El especialista no administra cuentas, roles, archivos, credenciales de WhatsApp, respuestas del paciente, reintentos, métricas ni reportes en el MVP. El catálogo completo de exclusiones se mantiene en [fuera-del-alcance-MVP.md](../00-producto/fuera-del-alcance-MVP.md).

