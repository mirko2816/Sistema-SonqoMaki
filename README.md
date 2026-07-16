# Sistema Sonqo Maki

Aplicación web para registrar pacientes, preparar planes con rutinas, enviar recordatorios mediante WhatsApp y permitir que el paciente consulte la rutina vigente desde un enlace seguro.

## Estado

La documentación del MVP está normalizada para iniciar la implementación. La primera validación se ejecutará localmente; el despliegue productivo queda fuera de esta fase.

## Decisiones principales del MVP

- Un único tipo de usuario autenticado: especialista.
- Laravel, Blade, Alpine.js, Tailwind CSS y PostgreSQL.
- Monolito modular renderizado por el servidor.
- Zona horaria fija `America/Lima`.
- Hasta dos recordatorios diarios por plan.
- WhatsApp Cloud API con resultado inmediato, sin webhooks ni reintentos automáticos.
- Pacientes archivados mediante eliminación lógica.
- Biblioteca de ejercicios y biblioteca de rutinas reutilizables.
- Enlace público exclusivo del plan, generado durante su primera activación.

## Documentación

```text
00-producto/
01-requisitos/
02-casos-de-uso/
03-features/
04-arquitectura/
05-integraciones/
06-trazabilidad/
```

## Orden de lectura

1. [Visión del producto](00-producto/vision-del-producto.md)
2. [Alcance del MVP](00-producto/alcance-mvp.md)
3. [Reglas de negocio](01-requisitos/reglas-de-negocio.md)
4. [Casos de uso](02-casos-de-uso/README.md)
5. [Arquitectura general](04-arquitectura/arquitectura-general.md)
6. [Modelo de datos](04-arquitectura/modelo-de-datos.md)
7. [Matriz de trazabilidad](06-trazabilidad/matriz-de-trazabilidad.md)
