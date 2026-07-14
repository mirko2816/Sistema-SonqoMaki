# Reglas de negocio - Sonqo Maki

> Estado: definicion inicial para el MVP  
> Fecha: 13 de julio de 2026  
> Alcance: pacientes, planes de ejercicios, rutinas y recordatorios

## 1. Pacientes

### RN-PAC-001 Telefono unico por paciente

Cada paciente debe tener un unico numero de telefono WhatsApp y un mismo numero no puede estar asociado a mas de un paciente registrado.

La unicidad debe comprobarse usando el numero normalizado con el codigo de pais `+51`, sin espacios, guiones, parentesis ni otras diferencias de formato.

La regla aplica tanto a pacientes activos como inactivos. Si un paciente es eliminado definitivamente, su numero puede utilizarse en un nuevo registro.

El sistema debe impedir la creacion o edicion de un paciente cuando el telefono normalizado ya pertenezca a otro paciente.

## 2. Planes de ejercicios

### RN-PLA-001 Multiples planes por paciente

Un paciente puede tener uno o mas planes de ejercicios. No se establece un numero maximo de planes por paciente para el MVP.

Cada plan pertenece a un solo paciente y debe conservar de forma independiente:

- su rango de fechas y estado;
- sus rutinas y ejercicios configurados;
- sus recordatorios;
- su enlace publico;
- su historial tecnico de envios.

La creacion, pausa, finalizacion o eliminacion de un plan no debe modificar los demas planes del paciente.

### RN-PLA-002 Planes activos con fechas superpuestas

Un paciente puede tener dos o mas planes activos cuyos rangos de fechas se superpongan total o parcialmente.

Cada plan puede atender una condicion o zona corporal diferente y mantiene sus propias rutinas. En una misma fecha, el paciente puede tener una rutina vigente por cada plan activo y debe poder consultar y realizar todas las que correspondan.

La superposicion entre planes no constituye un error y no debe impedir su activacion ni el envio de sus respectivos recordatorios.

## 3. Rutinas

### RN-RUT-001 Rutinas dentro del rango del plan

Toda rutina debe pertenecer a un plan y sus fechas de inicio y fin deben estar incluidas dentro del rango de fechas de ese plan.

Las fechas de inicio y fin del plan y de las rutinas se consideran inclusivas. La fecha de inicio de una rutina no puede ser posterior a su fecha de fin.

El sistema debe rechazar la creacion o edicion de una rutina que quede total o parcialmente fuera del rango de su plan.

### RN-RUT-002 Rutinas sin superposicion

Dos rutinas pertenecientes al mismo plan no pueden cubrir una misma fecha.

Si una rutina termina el dia `D`, la siguiente puede comenzar como minimo el dia `D + 1`. La validacion se aplica al crear o editar rutinas y al modificar las fechas del plan.

Esta restriccion se evalua dentro de cada plan; las rutinas de planes distintos no se comparan entre si.

### RN-RUT-003 Cobertura continua del plan

Para que un plan pueda usarse y enviar recordatorios, sus rutinas deben cubrir todos los dias del rango del plan sin superposiciones ni dias vacios.

Cuando exista mas de una rutina, la siguiente debe comenzar el dia posterior al fin de la anterior. Como consecuencia, para cada fecha del plan debe existir exactamente una rutina vigente.

El sistema puede permitir guardar temporalmente un plan incompleto durante su configuracion, pero no debe activarlo ni enviar recordatorios hasta que cumpla esta regla.

## 4. Recordatorios

### RN-REC-001 Maximo de recordatorios diarios por plan

Cada plan de ejercicios puede tener como maximo dos horarios de recordatorio para un mismo dia de la semana.

El limite se aplica por plan, no por paciente. Por lo tanto, un paciente con varios planes activos puede recibir recordatorios de cada uno de ellos.

Los dos horarios de un mismo plan y dia deben ser distintos. El sistema debe impedir guardar una configuracion que exceda el limite o duplique un horario.

### RN-REC-002 Condiciones de envio

Un recordatorio solo puede enviarse cuando, en el momento programado:

- el paciente esta activo y tiene un telefono valido;
- el paciente tiene consentimiento WhatsApp registrado;
- el plan esta activo y la fecha actual esta dentro de su rango;
- los recordatorios del plan estan activos;
- existe exactamente una rutina vigente para la fecha;
- el enlace publico del plan dirige a contenido util.

Si alguna condicion no se cumple, el sistema no debe intentar el envio.

### RN-REC-003 Un solo envio por ejecucion programada

Cada combinacion de plan, fecha y horario programado debe producir como maximo un intento de envio, incluso si el proceso de programacion se ejecuta mas de una vez.

## 5. Pagina publica

### RN-PUB-001 Resolucion actual de enlaces publicos

El enlace publico identifica de forma segura a un plan y no fija una rutina concreta. Por ello, un enlace enviado anteriormente debe resolverse nuevamente cada vez que se abre, usando el estado actual del plan y la fecha calendario de `America/Lima`.

El sistema debe aplicar esta prioridad:

1. Validar que el enlace sea autentico, no este revocado y corresponda a un plan existente.
2. Si el plan esta en pausa, mostrar el estado estatico de plan pausado, independientemente de que exista una rutina para la fecha.
3. Si el plan esta finalizado o la fecha actual es posterior a su fecha de fin, mostrar el estado estatico de plan finalizado.
4. Si el plan esta activo y la fecha actual esta dentro de su rango, localizar exactamente una rutina cuya fecha inicial y final inclusivas contengan la fecha actual y mostrarla.
5. En cualquier otro caso, mostrar un estado generico no disponible.

La resolucion no debe cambiar el plan, reactivar recordatorios ni permitir el acceso directo a una rutina pasada o futura. La fecha posterior al fin prevalece sobre un estado `activo` que aun no haya sido actualizado de forma persistente.

## 6. Decisiones relacionadas

- El limite de dos recordatorios se controla por plan, aunque un paciente tenga varios planes.
- Un paciente puede tener varios planes activos y rutinas vigentes el mismo dia.
- Un telefono queda disponible para otro paciente despues de eliminar definitivamente al paciente que lo tenia asignado.
- Las fechas se manejan por dia calendario en la zona horaria de la clinica, inicialmente `America/Lima`.
- Las reglas de rango, no superposicion y continuidad deben validarse nuevamente cuando cambien las fechas de un plan o de cualquiera de sus rutinas.
- Los enlaces publicos antiguos conservan su utilidad porque apuntan al plan, pero siempre muestran el resultado que corresponde al momento de la consulta.
