
# Universidad Social — Guía de instalación (macOS y Windows)

Esta guía explica, paso a paso, cómo poner a funcionar el proyecto en **cualquier computadora**, incluyendo:

* Instalación de dependencias
* Configuración del entorno (`.env`)
* Creación de base de datos
* **Ejecución de los 2 archivos `.sql` por terminal**
* Arranque del servidor
* Cómo crear el primer usuario **administrador**

> **Stack**: Laravel 12, PHP 8.4+ (8.2+ funciona), MySQL/MariaDB, Composer, Node.js

---

## 1) Requisitos

Asegúrate de tener instalado:

* **Git**
* **PHP 8.2+** (recomendado 8.4) con extensiones: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`.
* **Composer** ([https://getcomposer.org](https://getcomposer.org))
* **Node.js 18+** y **npm**
* **MySQL/MariaDB**

  * En macOS puedes usar **MAMP** (MySQL suele estar en host `127.0.0.1`, puerto `8889`, usuario `root`, clave `root`).
  * En Windows puedes usar **XAMPP/WAMP** (MySQL típico en puerto `3306`, usuario `root`, **sin clave** por defecto).

> Si usas MAMP/XAMPP, enciende **Apache** y **MySQL** antes de continuar.

---

## 2) Obtener el código

```bash
git clone <URL_DEL_REPO> universidad-social-UTP
cd universidad-social-UTP
```

---

## 3) Instalar dependencias

```bash
composer install
npm install
```

---

## 4) Preparar el archivo `.env`

Copia el ejemplo y genera clave de aplicación:

**macOS / Linux**

```bash
cp .env.example .env
php artisan key:generate
```

**Windows (PowerShell)**

```powershell
copy .env.example .env
php artisan key:generate
```

### 4.1) Configura variables principales en `.env`

Edita `.env` y ajusta al entorno local:

```dotenv
APP_NAME="Universidad Social UTP"
APP_ENV=local
APP_KEY=base64:GENERADA_POR_ARTISAN
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# MySQL (ajusta HOST/PORT/USER/PASS según MAMP/XAMPP)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306         # MAMP usa 8889 por defecto
DB_DATABASE=universidad_social
DB_USERNAME=root
DB_PASSWORD=         # MAMP: root / XAMPP: (vacío por defecto)

# Si usas MAMP (por defecto):
# DB_PORT=8889
# DB_PASSWORD=root
```

---

## 5) Crear la base de datos y **cargar los 2 .sql por terminal**

> En el repo coloca tus archivos `.sql` (por ejemplo `db/1_schema.sql` y `db/2_seed.sql`).
> Sustituye rutas/nombres de archivo si los tuyos son distintos.

### 5.1) macOS con **MAMP** (puerto MySQL 8889)

**Crear la base de datos**

```bash
/Applications/MAMP/Library/bin/mysql -u root -proot -h 127.0.0.1 -P 8889 -e "CREATE DATABASE IF NOT EXISTS universidad_social CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**Importar el esquema**

```bash
/Applications/MAMP/Library/bin/mysql -u root -proot -h 127.0.0.1 -P 8889 universidad_social < db/1_schema.sql
```

**Importar los datos (seed)**

```bash
/Applications/MAMP/Library/bin/mysql -u root -proot -h 127.0.0.1 -P 8889 universidad_social < db/2_seed.sql
```

> Si tus `.sql` ya incluyen `CREATE DATABASE`/`USE`, puedes omitir el primer paso.

### 5.2) Windows con **XAMPP/WAMP** (puerto MySQL 3306)

**Crear la base de datos**

```powershell
mysql -u root -h 127.0.0.1 -P 3306 -e "CREATE DATABASE IF NOT EXISTS universidad_social CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**Importar el esquema**

```powershell
mysql -u root -h 127.0.0.1 -P 3306 universidad_social < db\1_schema.sql
```

**Importar los datos (seed)**

```powershell
mysql -u root -h 127.0.0.1 -P 3306 universidad_social < db\2_seed.sql
```

> Si tu `root` tiene contraseña, agrega `-p` (te pedirá la clave), o `-pTU_CLAVE` (sin espacios).

---

## 6) Ajustes de permisos (necesarios en macOS/Linux)

Para evitar problemas de caché y subida de archivos:

```bash
chmod -R ug+rwx storage bootstrap/cache
php artisan storage:link
```

En Windows, asegúrate de que tu usuario tenga permisos de **Modificar** sobre `storage` y `bootstrap/cache`. Luego:

```powershell
php artisan storage:link
```

---

## 7) Compilar assets (opcional en desarrollo)

```bash
npm run dev
# o para producción
# npm run build
```

---

## 8) Arrancar el servidor de desarrollo

```bash
php artisan serve
```

Visita: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

> Si usas MAMP/XAMPP con Apache, también puedes configurar un VirtualHost que apunte al directorio `public/` del proyecto. Para desarrollo, `php artisan serve` es suficiente.

---

## 9) Flujo básico para verificar

* Inicia sesión como **administrador**:

  * Verás el panel con **Propuestas pendientes**, **Activas** y **Finalizadas/Rechazadas**.
  * Acepta una propuesta → crea una Actividad **publicada**.
  * Cierra convocatoria, habilita y **cierra** la lista de asistencia.
    Al **cerrar la lista**, el sistema otorga **automáticamente** las horas a quienes asistieron y verás un mensaje de confirmación.

* Inicia sesión como **profesor/organización**:

  * Podrás **postular** actividades.
  * Si el admin habilita la lista (**compartida**), verás el botón **Tomar lista**.

* Inicia sesión como **estudiante**:

  * Verás tus **horas acumuladas** (más grandes en el encabezado).
  * Verás **convocatorias abiertas** con botón **Apuntarse** (se desactiva si ya estás inscrito).
  * Modal de confirmación antes de apuntarte.
  * Tabla de **activas** y **finalizadas** propias.

---

## 10) Comandos útiles

* **Limpiar cachés** (si algo no se refleja):

  ```bash
  php artisan optimize:clear
  ```

* **Verificar conexión DB**:

  ```bash
  php artisan tinker
  >>> DB::select('SELECT 1');
  ```

---

## 11) Problemas comunes y soluciones

* **No me conecta a la base de datos**
  Verifica `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` en `.env`.
  En **MAMP**, MySQL suele estar en **puerto 8889** y credenciales **root/root**.

* **Los `.sql` fallan**
  Asegúrate de que la **BD existe** y que importas en el **orden correcto**: primero **schema**, luego **seed**.
  Quita `CREATE DATABASE`/`USE` del `.sql` si te da conflicto y usa la BD creada en el paso 5.

* **403 al tomar lista**
  Verifica que el usuario tenga rol **profesor** u **organización** y que la ruta tenga el middleware correcto.
  Asegúrate de que la lista esté en estado **compartida** y la actividad tenga `attendance_enabled=1`.

* **Storage/archivos no visibles**
  Ejecuta `php artisan storage:link` y revisa permisos de `storage` + `bootstrap/cache`.

---

## 12) Estructura de los archivos `.sql` (sugerencia)

* `db/1_schema.sql` → DDL (CREATE TABLEs, índices, constraints)
* `db/2_seed.sql`   → Datos iniciales (roles, usuarios de prueba, etc.)

Puedes usar otros nombres, pero **mantén el orden** de importación.

---

## 13) ¿Necesito migraciones?

Este proyecto está preparado para trabajar directamente con los **.sql** que ya incluyen estructura y datos.
Si prefieres migraciones, revísalas antes y **no mezcles** ambos enfoques para evitar conflictos.

---

## 14) Variables extra útiles

```dotenv
# Ajusta la hora/idioma si lo necesitas
APP_TIMEZONE=America/Panama
APP_LOCALE=es
```
