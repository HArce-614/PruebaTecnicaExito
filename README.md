# Prueba Técnica — Gestión de Eventos · Drupal 10

Módulo custom para Drupal 10 que permite crear eventos, listarlos en una página pública, e inscribir usuarios en tiempo real vía AJAX. Incluye tema visual personalizado alineado con la marca Móvil Éxito.

**Stack:** PHP 8.4 · MySQL 9.1 · Apache 2.4 · Drupal 10.3 · WAMP64

---

## Requisitos previos

| Software | ¿Cómo se instala? |
|---|---|
| **WAMP64** (incluye PHP, MySQL y Apache) | [wampserver.com](https://www.wampserver.com/) → descargar e instalar |
| **Composer** (gestor de dependencias de PHP) | [getcomposer.org/download](https://getcomposer.org/download/) → descargar `Composer-Setup.exe` e instalar. Cuando pida la ruta de PHP, seleccionar `C:\wamp64\bin\php\php8.4.0\php.exe` |
| **Git** | [git-scm.com/download/win](https://git-scm.com/download/win) → descargar e instalar |

> **Drush** (la herramienta CLI de Drupal) **no se instala aparte**. Se descarga automáticamente en el paso 3 como parte de `composer install`.

### Verificar que todo está instalado

Abrir una ventana **nueva** de PowerShell y ejecutar:

```powershell
php -v
composer --version
git --version
```

Los tres comandos deben mostrar sus versiones sin error. Si alguno falla, reinstalar el software correspondiente y **abrir una ventana nueva de PowerShell** (las ventanas viejas no detectan programas recién instalados).

---

## Paso 1 — Descargar el proyecto

```powershell
cd C:\wamp64\www
git clone <url-del-repositorio> PruebaTecnicaExito
```

Si el proyecto se recibió como ZIP: descomprimir y colocar la carpeta en `C:\wamp64\www\PruebaTecnicaExito\`.

Verificar que existe el archivo `C:\wamp64\www\PruebaTecnicaExito\composer.json`.

---

## Paso 2 — Iniciar WAMP

1. Abrir WAMP desde el menú inicio de Windows
2. Esperar a que el ícono en la barra de tareas (esquina inferior derecha, cerca del reloj) se ponga **verde**
3. Verde = Apache y MySQL están corriendo

> Si el ícono queda naranja o rojo: otro programa está usando el puerto 80 (Skype, IIS). Cerrar ese programa y reiniciar WAMP.

---

## Paso 3 — Instalar dependencias con Composer

Este comando descarga Drupal, Drush y todas las librerías necesarias:

```powershell
cd C:\wamp64\www\PruebaTecnicaExito
composer install
```

Tiempo estimado: 2–5 minutos según la conexión a internet.

Verificar que Drush quedó instalado:

```powershell
vendor\bin\drush --version
```

Debe mostrar `Drush Commandline Tool 12.x.x`.

> **¿Por qué `vendor\bin\drush` y no solo `drush`?**
> Drush se instala dentro del proyecto (en la carpeta `vendor/`), no globalmente. Siempre se ejecuta con la ruta `vendor\bin\drush` desde la carpeta del proyecto.

---

## Paso 4 — Crear la base de datos

Drupal necesita una base de datos MySQL vacía. **No se necesita importar ningún archivo `.sql`** — Drupal crea todas las tablas automáticamente en el paso 5.

### Opción A: Con phpMyAdmin (interfaz gráfica)

1. Abrir el navegador e ir a `http://localhost/phpmyadmin/`
2. Iniciar sesión con usuario `root` y contraseña vacía (dejar el campo en blanco)
3. En el panel izquierdo, hacer clic en **"New"** (o "Nueva")
4. Nombre de la base de datos: `prueba_tecnica_exito`
5. Cotejamiento: `utf8mb4_unicode_ci`
6. Clic en **"Create"** (Crear)

### Opción B: Por línea de comandos

```powershell
C:\wamp64\bin\mysql\mysql9.1.0\bin\mysql.exe -u root
```

> Si la versión de MySQL es diferente, buscar la carpeta correcta dentro de `C:\wamp64\bin\mysql\`.

En la consola de MySQL (prompt `mysql>`), escribir:

```sql
CREATE DATABASE prueba_tecnica_exito CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Presionar Enter. Debe aparecer `Query OK`. Luego escribir `exit` para salir.

---

## Paso 5 — Instalar Drupal

### Opción A: Por línea de comandos con Drush (recomendado)

```powershell
cd C:\wamp64\www\PruebaTecnicaExito

vendor\bin\drush site:install standard --db-url=mysql://root:@localhost/prueba_tecnica_exito --site-name="Prueba Técnica Móvil Éxito" --account-name=admin --account-pass=AdminPassword123 -y
```

Qué hace cada parte:
- `site:install standard` → instala Drupal con el perfil estándar
- `mysql://root:@localhost/prueba_tecnica_exito` → conexión a MySQL (usuario `root`, sin contraseña, base de datos `prueba_tecnica_exito`)
- `--account-name=admin` → crea el usuario administrador
- `--account-pass=AdminPassword123` → contraseña del administrador
- `-y` → confirma sin preguntar

Tiempo estimado: 1–3 minutos.

### Opción B: Por interfaz web

1. Abrir el navegador e ir a `http://localhost/PruebaTecnicaExito/web`
2. Drupal mostrará el asistente de instalación
3. Idioma: Español (o English)
4. Perfil: Standard
5. Base de datos:
   - Tipo: MySQL
   - Nombre: `prueba_tecnica_exito`
   - Usuario: `root`
   - Contraseña: *(vacío)*
   - Host: `localhost`, Puerto: `3306`
6. Configurar el sitio:
   - Nombre: Prueba Técnica Móvil Éxito
   - Usuario: admin
   - Contraseña: AdminPassword123
7. Clic en "Guardar y continuar"

### Verificar

Abrir `http://localhost/PruebaTecnicaExito/web` → debe cargar la página de inicio de Drupal. Iniciar sesión con `admin` / `AdminPassword123`.

---

## Paso 6 — Habilitar el módulo y el tema

```powershell
cd C:\wamp64\www\PruebaTecnicaExito

vendor\bin\drush en custom_events -y
vendor\bin\drush theme:enable movilexito_theme
vendor\bin\drush config:set system.theme default movilexito_theme -y
vendor\bin\drush cr
```

Qué hace cada comando:
- `drush en custom_events -y` → habilita el módulo de eventos
- `drush theme:enable movilexito_theme` → habilita el tema visual
- `drush config:set system.theme default movilexito_theme -y` → lo establece como tema predeterminado
- `drush cr` → limpia la caché de Drupal

---

## Paso 7 — Verificar que todo funciona

1. Ir a `http://localhost/PruebaTecnicaExito/web` — el sitio debe verse con el diseño de Móvil Éxito (blanco, púrpura, amarillo)
2. Iniciar sesión como `admin` / `AdminPassword123`
3. Ir a `/events/add` → crear un evento (título, descripción, seleccionar país de la lista, fecha, marcar Published)
4. Ir a `/events` → debe aparecer la tarjeta del evento
5. Cerrar sesión → crear una cuenta normal desde `/user/register`
6. Iniciar sesión con la cuenta nueva → ir a `/events` → clic en "Registrarse"
7. El botón cambia a "Ya registrado ✓" y el contador sube **sin recargar la página**

---

## Credenciales

```
URL:        http://localhost/PruebaTecnicaExito/web
Usuario:    admin
Contraseña: AdminPassword123
```

## URLs principales

| Página | URL |
|---|---|
| Inicio | `/` |
| Eventos (público) | `/events` |
| Crear evento (admin) | `/events/add` |
| Login | `/user/login` |
| Registro de cuenta | `/user/register` |
| Admin | `/admin` |
| Gestionar eventos | `/admin/content/events` |
| Ver inscripciones | `/admin/content/event-registrations` |

---

## Solución de problemas

### `composer install` falla con "php is not recognized"

PHP no está en el PATH. Solución:
1. Menú inicio → buscar "variables de entorno" → abrir
2. Variables del sistema → `Path` → Editar
3. Agregar: `C:\wamp64\bin\php\php8.4.0\`
4. Aceptar todo → **abrir una ventana nueva de PowerShell**

### `vendor\bin\drush` falla con "not recognized"

No se ejecutó `composer install` o se está en la carpeta equivocada. Solución:

```powershell
cd C:\wamp64\www\PruebaTecnicaExito
composer install
vendor\bin\drush --version
```

### Error de conexión a base de datos al instalar Drupal

1. Verificar que WAMP está corriendo (ícono verde)
2. Verificar que la base de datos `prueba_tecnica_exito` existe en phpMyAdmin (`http://localhost/phpmyadmin/`)
3. WAMP usa usuario `root` sin contraseña. El `--db-url` correcto es: `mysql://root:@localhost/prueba_tecnica_exito`

### La lista de países está vacía al crear un evento

La API de REST Countries no pudo contactarse. Verificar conexión a internet y que `https://restcountries.com/v3.1/all?fields=name` abre en el navegador.

### El sitio se ve sin estilos (sin colores ni diseño)

El tema no está activo. Ejecutar:

```powershell
cd C:\wamp64\www\PruebaTecnicaExito
vendor\bin\drush config:set system.theme default movilexito_theme -y
vendor\bin\drush cr
```

Además, limpiar la caché del navegador: `Ctrl + Shift + Supr`.

### Error 404 en `/events`

El módulo no está habilitado:

```powershell
vendor\bin\drush en custom_events -y
vendor\bin\drush cr
```

### El botón "Registrarse" da error 403

Token de seguridad expirado. Cerrar sesión, limpiar cookies del navegador (`F12` → Application → Cookies → Clear), volver a iniciar sesión e intentar de nuevo.
