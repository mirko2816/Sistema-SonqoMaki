# Sistema-SonqoMaki

## El proyecto

---

Una aplicación interna para registrar pacientes, asignarles una rutina, compartirla mediante un enlace seguro y enviar recordatorios por WhatsApp. Puedes revisar la vision y caracteristicas principales generales del producto en [vision-del-producto.md](vision-del-producto.md)

## Estado

---

🚧 **Proyecto en desarrollo**, ahora estamos en una fase de depuración de casos de uso y alcance del sistema. Se esta priorizando una entraga del producto que empiece a resolver problemas, e ir incrementando caracteristicas mas adelante. Aun no hay código. 

## Estructura esperada del proyecto

---

```
docs/
├── 00-producto/
│ ├── vision-del-producto.md
│ ├── alcance-mvp.md
│ ├── fuera-de-alcance.md
│ └── glosario.md
│
├── 01-requisitos/
│ ├── requisitos-funcionales.md
│ ├── requisitos-no-funcionales.md
│ └── reglas-de-negocio.md
│
├── 02-casos-de-uso/
│ ├── autenticacion/
│ │ └── CU-AUTH-001-iniciar-sesion.md
│ ├── pacientes/
│ │ ├── CU-PAC-001-registrar-paciente.md
│ │ └── CU-PAC-002-editar-paciente.md
│ ├── ejercicios/
│ │ ├── CU-EJ-001-registrar-ejercicio.md
│ │ └── CU-EJ-002-editar-ejercicio.md
│ ├── planes/
│ │ └── CU-PLAN-001-crear-plan.md
│ ├── rutinas/
│ │ └── CU-RUT-001-configurar-rutina.md
│ ├── recordatorios/
│ │ └── CU-REC-001-programar-recordatorios.md
│ ├── pagina-publica/
│ │ └── CU-WEB-001-visualizar-rutina.md
│ └── whatsapp/
│ └── CU-WA-001-enviar-recordatorio.md
│
├── 03-features/
│ ├── autenticacion/
│ │ └── iniciar-sesion.feature
│ ├── pacientes/
│ │ ├── registrar-paciente.feature
│ │ └── editar-paciente.feature
│ ├── planes/
│ │ └── crear-plan.feature
│ ├── rutinas/
│ │ └── visualizar-rutina-publica.feature
│ └── recordatorios/
│ ├── programar-recordatorio.feature
│ └── enviar-recordatorio-whatsapp.feature
│
├── 04-arquitectura/
│ ├── arquitectura-general.md
│ ├── modelo-de-datos.md
│ ├── flujos-del-sistema.md
│ └── adr/
│ ├── ADR-001-stack-tecnologico.md
│ ├── ADR-002-enlace-publico-por-plan.md
│ ├── ADR-003-whatsapp-cloud-api.md
│ └── ADR-004-unico-tipo-de-usuario.md
│
├── 05-integraciones/
│ └── whatsapp-cloud-api.md
│
└── 06-trazabilidad/
 └── matriz-de-trazabilidad.md
```
