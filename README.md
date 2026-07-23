# Sistema Sonqo Maki

Aplicación web para registrar pacientes, preparar planes con rutinas, enviar recordatorios mediante WhatsApp y permitir que el paciente consulte la rutina vigente desde un enlace seguro.

## Estado

La base técnica del MVP está inicializada con Laravel 12, Blade, Alpine.js, Tailwind CSS, PostgreSQL y Pest. Están disponibles la autenticación, pacientes, biblioteca de ejercicios, plantillas reutilizables y planes asignados con rutinas, ejercicios, estados, duplicación y enlace seguro preparado. La página pública, los recordatorios y WhatsApp se incorporarán en iteraciones posteriores.

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

Después de iniciar sesión en `/iniciar-sesion`, el especialista es dirigido a `/dashboard`. Pacientes está en `/pacientes`, ejercicios en `/ejercicios`, plantillas en `/rutinas` y planes asignados en `/planes`. Desde un plan se configuran rutinas manuales o copiadas, ejercicios y orden; también se valida su activación, se cambia de estado y se duplica. El dashboard muestra una fila por plan activo y declara honestamente los recordatorios como “Sin configurar”.

La primera activación crea un token de al menos 32 bytes aleatorios. PostgreSQL guarda su hash SHA-256 para resolución, una copia cifrada con `APP_KEY` para construir futuros recordatorios y un prefijo no sensible para diagnóstico; nunca guarda ni registra el token en texto plano.

La finalización automática puede ejecutarse manualmente con `php artisan plans:finish-expired`. El scheduler la programa diariamente a las 00:05 en `America/Lima`; en operación continua debe mantenerse activo `php artisan schedule:work` o un cron equivalente.

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

## Organización modular

Los módulos funcionales viven bajo `app/Modules`. Pacientes incluye creación, edición, estado y archivo; Ejercicios centraliza normalización y retiro; RoutineTemplates encapsula copias reutilizables; Plans centraliza creación, composición, cobertura, activación, estados, duplicación, archivo técnico y finalización automática. Las rutas, controladores, solicitudes y vistas mantienen las convenciones de Laravel.

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
