# Sistema Sonqo Maki

Aplicación web para registrar pacientes, preparar planes con rutinas, enviar recordatorios mediante WhatsApp y permitir que el paciente consulte la rutina vigente desde un enlace seguro.

## Estado

La base técnica del MVP está inicializada con Laravel 12, Blade, Alpine.js, Tailwind CSS, PostgreSQL y Pest. Están disponibles la autenticación, pacientes, biblioteca de ejercicios, plantillas reutilizables, planes asignados, página pública mediante enlace seguro, configuración de recordatorios por plan y ejecución programada mediante WhatsApp Cloud API. La interfaz del historial técnico se incorporará en una iteración posterior.

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

## Requisitos locales

- PHP 8.2 o posterior compatible con Laravel 12, con `curl`, `dom`, `fileinfo`, `mbstring`, `openssl`, `pdo_pgsql`, `pgsql`, `tokenizer`, `xml` y `zip`.
- Composer 2.
- Node.js 22 y npm 10 o versiones compatibles.
- PostgreSQL en una versión con soporte vigente.

En XAMPP para Windows, habilita en `php.ini` las extensiones `extension=pdo_pgsql`, `extension=pgsql` y `extension=zip`. Asegúrate también de que PHP, Composer, Node, npm y `psql` estén disponibles en `PATH`.

## Instalación

```bash
composer run setup
```

El script instala dependencias PHP y JavaScript, crea `.env` desde `.env.example` si hace falta, genera `APP_KEY` y compila los recursos. No ejecuta migraciones automáticamente para evitar apuntar por error a una base de datos incorrecta.

También puedes ejecutar los pasos por separado:

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
```

En PowerShell usa `Copy-Item .env.example .env` en lugar de `cp`.

## Configuración de PostgreSQL

Crea dos bases separadas:

```sql
CREATE DATABASE sonqo_maki;
CREATE DATABASE sonqo_maki_test;
```

Configura las credenciales locales únicamente en `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sonqo_maki
DB_USERNAME=postgres
DB_PASSWORD=
DB_SSLMODE=prefer
```

Para comprobar la conexión:

```bash
php artisan db:show --database=pgsql
```

Ejecuta las migraciones con:

```bash
composer run migrate
```

La migración de planes habilita la extensión PostgreSQL `btree_gist` para impedir en la propia base de datos que dos rutinas no archivadas del mismo plan se superpongan. El usuario de migraciones debe poder ejecutar `CREATE EXTENSION IF NOT EXISTS btree_gist`.

Las sesiones se almacenan en PostgreSQL mediante `SESSION_DRIVER=database`. En un entorno HTTPS configura además `SESSION_SECURE_COOKIE=true`; las cookies ya se restringen a HTTP y usan `SameSite=lax` por defecto.

## Crear la cuenta inicial del especialista

No existe registro público. Después de ejecutar las migraciones, crea la cuenta desde una terminal:

```bash
php artisan specialist:create
```

El comando solicita el correo y pide dos veces una contraseña oculta de al menos 12 caracteres. También puedes proporcionar únicamente el correo como argumento:

```bash
php artisan specialist:create especialista@ejemplo.com
```

No pases la contraseña como argumento ni la escribas en archivos de configuración. El comando normaliza el correo, genera el hash con la configuración segura de Laravel y rechaza cuentas duplicadas, incluso si cambia el uso de mayúsculas.

## Ejecución

Inicia Laravel:

```bash
composer run serve
```

En otra terminal inicia Vite durante el desarrollo:

```bash
npm run dev
```

La aplicación estará disponible normalmente en `http://127.0.0.1:8000`.

Después de iniciar sesión en `/iniciar-sesion`, el especialista es dirigido a `/dashboard`. Pacientes está en `/pacientes`, ejercicios en `/ejercicios`, plantillas en `/rutinas`, planes asignados en `/planes` y la vista general de recordatorios en `/recordatorios`. Desde un plan se configuran rutinas, ejercicios y hasta dos horarios distintos por día en la zona fija `America/Lima`; la programación puede activarse o pausarse sin cambiar el estado del plan. El dashboard distingue planes sin horarios, con recordatorios configurados en pausa y con recordatorios activos.

La primera activación crea un token de al menos 32 bytes aleatorios. PostgreSQL guarda su hash SHA-256 para resolución, una copia cifrada con `APP_KEY` para construir futuros recordatorios y un prefijo no sensible para diagnóstico; nunca guarda ni registra el token en texto plano. La página pública usa la ruta estable `/mi-rutina/{token}`, no requiere sesión y siempre vuelve a evaluar el plan y la rutina vigente con la fecha de `America/Lima`.

`APP_URL` debe contener la base pública real de cada entorno. Para pruebas desde otro dispositivo, configura allí la URL HTTPS estable del túnel antes de activar planes o componer enlaces; no codifiques el dominio en el código. La página pública evita indexación, referencias salientes y caché persistente, pero el servidor o proveedor del túnel también debe evitar registrar la ruta completa porque esta contiene el token secreto.

La finalización automática puede ejecutarse manualmente con `php artisan plans:finish-expired`. El scheduler la programa diariamente a las 00:05 en `America/Lima`. El motor de recordatorios se ejecuta cada minuto y puede diagnosticarse manualmente con `php artisan reminders:process-due`.

En operación continua debe mantenerse activo el scheduler en una terminal independiente:

```bash
php artisan schedule:work
```

El comando manual evalúa únicamente el minuto actual: no recupera horarios anteriores ni genera ejecuciones retroactivas. Cada combinación de plan, fecha local y hora se adquiere una sola vez; por eso no debe ejecutarse como simulación sobre datos reales si todavía no se desea consumir esa ejecución.

## Recursos frontend

```bash
npm run build
```

Blade renderiza la interfaz en el servidor. Alpine.js queda reservado para interacciones puntuales y Tailwind CSS se compila mediante Vite.

Para verificar la interfaz, inicia Laravel y Vite, accede con la cuenta del especialista y comprueba el dashboard tanto en celular como en escritorio. En celular, el botón de menú abre la navegación lateral; puede cerrarse con su botón, tocando fuera o con la tecla `Escape`.

## Pruebas

Pest usa PostgreSQL cuando una prueba necesita persistencia. Copia `.env.testing.example` a `.env.testing`, genera una clave y completa las credenciales locales si difieren de los valores de ejemplo:

```bash
cp .env.testing.example .env.testing
php artisan key:generate --env=testing
composer test
```

Las pruebas de autenticación y restricciones se ejecutan contra PostgreSQL real en `sonqo_maki_test`, nunca contra la base de desarrollo.

## Configuración y ejecución de recordatorios

Cada plan tiene una configuración propia e inicialmente inactiva. La migración crea configuraciones para los planes existentes y los flujos de creación y duplicación mantienen el mismo invariante para planes nuevos. Los horarios usan días ISO-8601 (`1` lunes a `7` domingo), borrado lógico y restricciones PostgreSQL contra días inválidos y duplicados activos.

El guardado se realiza en una transacción que bloquea la configuración, valida la programación completa, limita cada día a dos horarios y sincroniza altas, restauraciones y retiros. Archivar un paciente o un plan desactiva sus recordatorios sin borrar los horarios.

El scheduler localiza únicamente horarios no eliminados que coinciden con el día ISO y minuto actual en `America/Lima`. Antes de llamar al proveedor vuelve a comprobar paciente, teléfono, consentimiento, plan, rango de fechas, configuración, cobertura de rutinas, rutina vigente con ejercicios y enlace público recuperable. Las omisiones quedan registradas sin contactar a WhatsApp.

### WhatsApp Cloud API

Configura en `.env` una plantilla previamente aprobada por Meta cuyo cuerpo tenga dos parámetros de texto, en este orden: nombre del paciente y URL segura del plan. El texto representado por la plantilla debe ser:

```text
Hola {{1}}. Tu salud es importante. Recuerda realizar tu rutina de hoy: {{2}}.
```

Variables necesarias:

```dotenv
WHATSAPP_ACCESS_TOKEN=
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_GRAPH_VERSION=v23.0
WHATSAPP_TEMPLATE_NAME=sonqo_maki_daily_reminder
WHATSAPP_TEMPLATE_LANGUAGE=es_PE
WHATSAPP_CONNECT_TIMEOUT=5
WHATSAPP_TIMEOUT=10
```

El token y el identificador reales solo deben existir en el `.env` no versionado. El adaptador envía una plantilla al endpoint `/{phone-number-id}/messages` de Graph API conforme al contrato oficial de [WhatsApp Cloud API mantenido por Meta](https://www.postman.com/meta/whatsapp-business-platform/documentation/wlk6lh4/whatsapp-cloud-api). La versión, el nombre y el idioma deben coincidir con la cuenta y la plantilla aprobada del entorno.

Las ejecuciones tienen estos resultados técnicos:

- `processing`: la instancia adquirió atómicamente el derecho a procesar; si una caída la deja así, no se reanuda automáticamente.
- `omitted`: una regla interna evitó contactar a WhatsApp.
- `accepted`: Meta aceptó inmediatamente la solicitud y devolvió un identificador. No significa entregado, leído ni realizado.
- `failed`: el proveedor rechazó o no respondió correctamente, o ocurrió un fallo interno.

No existen reintentos automáticos, webhooks ni confirmación posterior de entrega o lectura en esta etapa. La URL completa se envía necesariamente a Meta, pero el historial solo conserva la referencia al enlace; no almacena el token público. La consulta visual del historial (`CU-ENV-001`) todavía no está implementada.

## Organización modular

Los módulos funcionales viven bajo `app/Modules`. Pacientes incluye creación, edición, estado y archivo; Ejercicios centraliza normalización y retiro; RoutineTemplates encapsula copias reutilizables; Plans centraliza creación, composición, cobertura, activación, estados, duplicación, archivo técnico y finalización automática; Reminders centraliza la programación, adquisición idempotente, evaluación y ejecución; su adaptador de infraestructura aísla WhatsApp Cloud API. Las rutas, controladores, solicitudes y vistas mantienen las convenciones de Laravel.

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
