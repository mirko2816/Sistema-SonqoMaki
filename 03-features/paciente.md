# Features del paciente

> Actor: paciente sin cuenta  
> Alcance: MVP  
> Detalle de flujos y excepciones: caso de uso referenciado.

## Criterio documental

El paciente es un actor de consulta: no inicia sesión, no modifica información y no registra cumplimiento. Los recordatorios que recibe son consecuencia del sistema, no acciones iniciadas por él.

### FE-PAC-PUB-001 - Abrir la rutina desde un enlace seguro

**Como** paciente, **quiero** abrir el enlace recibido sin crear una cuenta, **para** consultar fácilmente la rutina que me corresponde hoy.  
**Detalle:** [CU-PUB-001](../02-casos-de-uso/pagina-publica/CU-PUB-001-consultar-rutina-vigente.md)

### FE-PAC-PUB-002 - Consultar las indicaciones de la rutina vigente

**Como** paciente, **quiero** ver los ejercicios en orden, sus indicaciones y materiales externos disponibles, **para** realizar correctamente mi rutina.  
**Detalle:** contenido visible en [CU-PUB-001](../02-casos-de-uso/pagina-publica/CU-PUB-001-consultar-rutina-vigente.md)

### FE-PAC-PUB-003 - Comprender la indisponibilidad del plan

**Como** paciente, **quiero** recibir un mensaje claro cuando el plan esté pausado, finalizado o no disponible, **para** saber qué hacer sin acceder a información incorrecta.  
**Detalle:** alternativas y excepciones de [CU-PUB-001](../02-casos-de-uso/pagina-publica/CU-PUB-001-consultar-rutina-vigente.md)

### FE-PAC-PUB-004 - Usar un enlace recibido anteriormente

**Como** paciente, **quiero** que un enlace recibido anteriormente consulte el estado actual de mi plan, **para** ver la rutina que me corresponde hoy o una explicacion clara cuando el plan ya no esta disponible.

**Criterios de aceptacion:**

- un plan en pausa muestra la pagina estatica de pausa;
- un plan finalizado o cuya fecha de fin ya paso muestra la pagina estatica de finalizacion;
- un plan activo dentro de su rango muestra exclusivamente la rutina vigente;
- el enlace no permite recuperar la rutina que estaba vigente cuando fue enviado;
- los enlaces invalidos, revocados o sin una unica rutina vigente no exponen informacion del paciente.

**Detalle:** resolucion definida en [CU-PUB-001](../02-casos-de-uso/pagina-publica/CU-PUB-001-consultar-rutina-vigente.md)

## Límites del actor

El paciente no puede ver rutinas pasadas o futuras, editar información, responder dentro del sistema, registrar dolor o cumplimiento ni consultar datos personales mediante el enlace público.
