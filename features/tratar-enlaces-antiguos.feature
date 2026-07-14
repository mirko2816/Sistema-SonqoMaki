# language: es
Caracteristica: Tratamiento de enlaces antiguos de un plan
  Como paciente sin cuenta
  Quiero abrir un enlace recibido anteriormente
  Para consultar el estado actual de mi plan sin acceder a rutinas pasadas o futuras

  Antecedentes:
    Dado que la fecha actual se obtiene usando la zona horaria "America/Lima"

  Escenario: El plan asociado al enlace esta en pausa
    Dado que el enlace es autentico, no esta revocado y corresponde a un plan existente
    Y que el plan esta "en pausa"
    Cuando el paciente abre el enlace antiguo
    Entonces el sistema muestra una pagina estatica con el mensaje "Su plan de ejercicios se encuentra pausado. Comuniquese con el especialista encargado"
    Y no muestra ninguna rutina del plan

  Escenario: El plan asociado al enlace esta finalizado
    Dado que el enlace es autentico, no esta revocado y corresponde a un plan existente
    Y que el plan esta "finalizado"
    Cuando el paciente abre el enlace antiguo
    Entonces el sistema muestra una pagina estatica con el mensaje "Plan de ejercicios finalizado. Para mas consultas, comuniquese con el especialista encargado"
    Y no muestra ninguna rutina del plan

  Escenario: La fecha final paso pero el estado persistido continua activo
    Dado que el enlace es autentico, no esta revocado y corresponde a un plan existente
    Y que el plan esta "activo"
    Y que la fecha actual es posterior a la fecha de fin del plan
    Cuando el paciente abre el enlace antiguo
    Entonces el sistema muestra la pagina estatica de plan finalizado
    Y no muestra ninguna rutina del plan

  Escenario: El plan esta activo y tiene una rutina vigente
    Dado que el enlace es autentico, no esta revocado y corresponde a un plan existente
    Y que el plan esta "activo"
    Y que la fecha actual esta dentro del rango inclusivo del plan
    Y que existe exactamente una rutina vigente para la fecha actual
    Cuando el paciente abre el enlace antiguo
    Entonces el sistema dirige a la pagina publica de la rutina vigente
    Y muestra solamente los ejercicios e indicaciones de esa rutina

  Esquema del escenario: El enlace no debe exponer una rutina cuando el plan no puede resolverse de forma segura
    Dado que <condicion>
    Cuando el paciente abre el enlace
    Entonces el sistema muestra un estado generico no disponible
    Y no muestra datos personales ni rutinas pasadas o futuras

    Ejemplos:
      | condicion                                                   |
      | el enlace es invalido                                       |
      | el enlace esta revocado                                     |
      | la fecha actual es anterior al inicio de un plan activo     |
      | no existe una rutina vigente para un plan activo            |
      | existen dos rutinas vigentes para el mismo plan y fecha     |
