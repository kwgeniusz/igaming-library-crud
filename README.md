# Biblioteca de libros

Aplicación para gestionar un catálogo compartido de libros con Laravel 13,
React e Inertia. Permite crear, consultar, editar y eliminar libros, filtrarlos
por categoría y gestionar categorías. Todas las operaciones requieren iniciar
sesión. Una categoría con libros asociados no puede eliminarse.

## Requisitos

- PHP 8.4 y Composer 2, incluidas las extensiones requeridas por Composer.
- Extensión PHP `pdo_mysql` para la conexión MySQL y `pdo_sqlite` para las pruebas.
- Node.js 22.18 o superior dentro de la rama 22, o Node.js 24.11 o superior
  dentro de la rama 24, según los requisitos de Vite+.
- npm y Git.
- MySQL en ejecución y un usuario con permisos sobre la base del proyecto.
- Acceso a Internet para instalar dependencias y descargar las fuentes durante
  la primera compilación del frontend.

Comprueba las herramientas disponibles:

```bash
php -v
composer --version
node --version
npm --version
```

## 1. Obtener el proyecto e instalar dependencias

```bash
git clone https://github.com/kwgeniusz/igaming-library-crud.git
cd igaming-library-crud
composer install
npm ci
composer check-platform-reqs
```

Si ya tienes el repositorio clonado, ejecuta los comandos de instalación desde
su raíz, donde se encuentra el archivo `artisan`. Usa los archivos de bloqueo
incluidos para conservar las versiones de las dependencias.

## 2. Configurar el entorno

Solo si todavía no tienes un archivo `.env`, copia la plantilla:

```bash
cp .env.example .env
```

En PowerShell, el comando equivalente es `Copy-Item .env.example .env`.
Si ya configuraste tu `.env`, conserva ese archivo y ajusta únicamente los
valores necesarios. No lo subas al repositorio.

Configura los siguientes valores para el entorno local:

```dotenv
APP_NAME=Biblioteca
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_LOCALE=es
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=igaming_db
DB_USERNAME=tu_usuario_mysql
DB_PASSWORD="tu_contraseña_mysql"

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=log

VITE_APP_NAME="${APP_NAME}"
```

Sustituye el usuario, la contraseña, el puerto y el nombre de base por los de tu
instalación. `MAIL_MAILER=log` permite trabajar localmente sin configurar SMTP.

Si `APP_KEY` está vacío, genera la clave de aplicación:

```bash
php artisan key:generate --no-interaction
```

Conserva una clave existente. Después de modificar la configuración, limpia su
caché para que Laravel lea los nuevos valores:

```bash
php artisan config:clear --no-interaction
```

## 3. Preparar la base de datos

Si la base todavía no existe, entra a MySQL con un usuario autorizado para crearla:

```bash
mysql -u root -p
```

Ejecuta lo siguiente en el cliente MySQL:

```sql
CREATE DATABASE IF NOT EXISTS igaming_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
EXIT;
```

El nombre debe coincidir con `DB_DATABASE`. Si la base ya está creada y la
conexión está habilitada, continúa con las migraciones.

### Alternativa: SQLite

Para ejecutar la aplicación sin un servidor MySQL, crea el archivo de base de
datos desde la raíz del proyecto:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

En `.env`, configura `DB_CONNECTION=sqlite` y elimina o comenta `DB_DATABASE`
para utilizar la ruta predeterminada `database/database.sqlite`. Si configuraste
`DB_URL`, elimínala o coméntala también para evitar que sustituya estos valores.
Después ejecuta `php artisan config:clear --no-interaction`.

## 4. Ejecutar migraciones y seeders

```bash
php artisan migrate --no-interaction
php artisan db:seed --no-interaction
php artisan migrate:status --no-interaction
```

Las migraciones crean las tablas de autenticación, sesiones, caché y colas,
además de `categories` y `books`. El comando `migrate` aplica únicamente las
migraciones pendientes y conserva los registros existentes.

Los seeders agregan tres categorías, cinco libros y un usuario de ejemplo:

| Campo      | Valor              |
| ---------- | ------------------ |
| Correo     | `test@example.com` |
| Contraseña | `password`         |

Los datos de ejemplo son para desarrollo local. Los seeders pueden repetirse sin
duplicar esos registros. Si el correo ya existe, conservan su contraseña y sus
datos actuales; también puedes crear una cuenta desde el formulario de registro.

## 5. Levantar la aplicación

Desde la raíz del proyecto, ejecuta:

```bash
composer run dev
```

Este script utiliza `php artisan dev` y levanta Laravel, Vite, el proceso de
colas y el visor de logs. Mantén la terminal abierta y accede a
`http://localhost:8000`. Inicia sesión y entra en **Libros** o **Categorías**.
Detén los procesos con `Ctrl+C`.

También puedes ejecutar solo los dos servidores necesarios para el CRUD en
terminales separadas:

**Terminal 1: Laravel**

```bash
php artisan serve --host=127.0.0.1 --port=8000 --no-interaction
```

**Terminal 2: Vite**

```bash
npm run dev
```

Abre la dirección del servidor Laravel, no la de Vite. Si eliges otro puerto
para Laravel, actualiza `APP_URL` y limpia la caché de configuración.

### Ejecución local con assets compilados

Para ejecutar el frontend sin mantener Vite abierto, detén primero su servidor
de desarrollo y luego ejecuta:

```bash
npm run build
php artisan serve --host=127.0.0.1 --port=8000 --no-interaction
```

La compilación genera los assets y el manifiesto en `public/build`; vuelve a
compilar después de cambiar el frontend. Wayfinder genera automáticamente los
helpers de rutas durante la compilación o al iniciar Vite.

## 6. Ejecutar las pruebas y comprobaciones

```bash
php artisan test --compact
```

La configuración de `phpunit.xml` utiliza SQLite en memoria para las pruebas,
con sesiones y caché en memoria, sin usar la base MySQL configurada en `.env`.

Para ejecutar únicamente las pruebas de la biblioteca:

```bash
php artisan test --compact tests/Feature/BookTest.php tests/Feature/CategoryTest.php
```

Otras comprobaciones disponibles:

```bash
composer run types:check
npm run types:check
npm run check
npm run build
```

`composer run types:check` ejecuta PHPStan; `npm run types:check` comprueba
TypeScript, y `npm run check` revisa formato y lint.

## Problemas frecuentes

- **`No application encryption key has been specified`**: comprueba que `.env`
  exista y ejecuta `php artisan key:generate --no-interaction` si `APP_KEY` está
  vacío; después limpia la caché de configuración.
- **`Connection refused` o `Access denied` al conectar a MySQL**: confirma que el
  servidor esté activo y revisa host, puerto, usuario, contraseña y permisos.
- **`Unknown database`**: crea la base indicada en `DB_DATABASE` antes de migrar.
- **`could not find driver`**: habilita `pdo_mysql` o `pdo_sqlite` en el PHP de la
  terminal, según la conexión que estés utilizando.
- **Tablas `sessions`, `cache`, `books` o `categories` inexistentes**: ejecuta
  `php artisan migrate --no-interaction` con la conexión correcta.
- **Pantalla sin estilos o recursos apuntando a un Vite apagado**: inicia
  `npm run dev`. Si prefieres usar la compilación, detén Vite, retira el archivo
  generado `public/hot` si quedó obsoleto y ejecuta `npm run build`.
- **Manifiesto de Vite ausente o imports de Wayfinder sin resolver**: ejecuta
  `npm run build`. También puedes regenerar los helpers con
  `php artisan wayfinder:generate --with-form --no-interaction`.
- **Puerto ocupado**: usa otro puerto con `php artisan serve --port=8001` y
  ajusta `APP_URL` para que coincida.

## Documentación oficial

- [Laravel 13: configuración](https://laravel.com/docs/13.x/configuration).
- [Laravel 13: base de datos](https://laravel.com/docs/13.x/database).
- [Laravel 13: migraciones](https://laravel.com/docs/13.x/migrations).
- [Laravel 13: seeders](https://laravel.com/docs/13.x/seeding).
- [Laravel 13: pruebas](https://laravel.com/docs/13.x/testing).
