# Matriz de trazabilidad del MVP

Esta matriz relaciona las capacidades aprobadas con sus requisitos, casos de uso y componentes de datos principales. El detalle normativo permanece en los documentos enlazados y no se duplica aquí.

| Capacidad | Requisitos y reglas | Casos de uso | Datos principales |
| --- | --- | --- | --- |
| Autenticación | `RF-AUT-001` a `RF-AUT-004` | `CU-AUT-001`, `CU-AUT-002` | `users`, `sessions` |
| Pacientes y consentimiento | `RF-PAC-001` a `RF-PAC-009`, `RN-PAC-001` | `CU-PAC-001` a `CU-PAC-005` | `patients` |
| Biblioteca de ejercicios | `RF-EJE-001` a `RF-EJE-007` | `CU-EJE-001` a `CU-EJE-004` | `exercises` |
| Planes | `RF-PLA-001` a `RF-PLA-006`, `RN-PLA-001` a `RN-PLA-003` | `CU-PLA-001` a `CU-PLA-004` | `plans` |
| Rutinas del plan | `RF-RUT-001` a `RF-RUT-007`, `RN-RUT-001` a `RN-RUT-003` | `CU-RUT-001` | `routines`, `routine_exercises` |
| Biblioteca de rutinas | `RF-RUT-008` | `CU-RUT-002`, `CU-RUT-003` | `routine_templates`, `routine_template_exercises` |
| Página pública | `RF-PUB-001` a `RF-PUB-008`, `RN-PUB-000`, `RN-PUB-001` | `CU-PUB-001` | `public_links`, `plans`, `routines` |
| Configuración de recordatorios | `RF-REC-001` a `RF-REC-005`, `RN-REC-001` | `CU-REC-001` | `reminder_configurations`, `reminder_schedules` |
| Ejecución de recordatorios | `RF-REC-006` a `RF-REC-009`, `RN-REC-002`, `RN-REC-003` | `CU-REC-002` | `reminder_executions` |
| WhatsApp e historial | `RF-WPP-001` a `RF-WPP-006` | `CU-REC-002`, `CU-ENV-001` | `reminder_executions` |
| Dashboard | `RF-UI-001`, `RF-UI-002` | `CU-UI-001` | consultas derivadas |
