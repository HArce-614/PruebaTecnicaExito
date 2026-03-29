# 📦 PRUEBA TÉCNICA - DESARROLLADOR FULL STACK (PHP + DRUPAL)

## Gestión de Eventos y Sistema de Inscripciones AJAX

---

## 🎯 Descripción del Proyecto

**Custom Events** es una solución completa de **gestión de eventos** con **inscripción de usuarios en tiempo real** mediante AJAX, desarrollada como módulo personalizado para **Drupal 10**.

### ✨ Características Principales

- ✅ **Gestión completa de eventos**: Crear, editar, publicar/despublicar y eliminar eventos
- ✅ **Consumo de API REST**: Integración automática con [REST Countries API](https://restcountries.com) para selección dinámica de países
- ✅ **Inscripciones AJAX**: Registro de usuarios sin recarga de página
- ✅ **Protección CSRF**: Validación de tokens de sesión Drupal en cada solicitud
- ✅ **Contador de inscritos dinámico**: Actualizaciones en tiempo real
- ✅ **Validación antiduplicados**: Prevención de registros múltiples mediante Symfony Validator
- ✅ **Caché inteligente**: 24 horas de caché para lista de países con fallback a caché obsoleto
- ✅ **Diseño responsive**: Conforme a la línea gráfica de Móvil Éxito
- ✅ **Accesibilidad WCAG 2.1**: HTML5 semántico, ARIA labels, live regions
- ✅ **100% nativo Drupal**: Sin dependencias externas innecesarias

---

## 📋 Requisitos Previos

Asegúrate de tener instalados:

| Requisito | Versión | Descripción |
|-----------|---------|-------------|
| **PHP** | `^8.4.0` | Motor de PHP 8.4 (Última versión) |
| **MySQL** | `^9.1.0` | Sistema de base de datos MySQL 9 |
| **Apache** | `^2.4.62` | Servidor web estable |
| **Composer** | `^2.0` | Gestor de dependencias PHP |
| **Drupal** | `^10.0` | Core de Drupal 10.0 o superior |
| **Drush** | `^11` o `^12` | Herramienta CLI de Drupal (recomendado) |
| **Git** | Cualquier versión | Para clonar el repositorio |

### Requisitos Opcionales
- **curl**: Para verificar llamadas a API
- **Make**: Para scripts de automatización
- **Docker**: Si prefieres ejecutar en contenedores

### Verificar Versiones Instaladas

```bash
# PHP
php -v

# Composer
composer --version

# Drush (si está instalado globalmente)
drush --version

# MySQL/MariaDB
mysql --version
```

---

## 🚀 Guía de Instalación Paso a Paso

### PASO 1: Clonar el Repositorio

```bash
# Navega a tu directorio de proyectos
cd /ruta/de/proyectos

# Clona el repositorio
git clone https://github.com/tu-usuario/PruebaTecnicaExito.git
cd PruebaTecnicaExito
```

**Si el proyecto ya está clonado**, simplemente navega al directorio:

```bash
cd /ruta/de/PruebaTecnicaExito
```

---

### PASO 2: Instalar Dependencias con Composer

Composer descargará todas las dependencias necesarias, incluyendo Drupal y sus módulos.

```bash
# Desde la raíz del proyecto
composer install

# Si necesitas actualizar dependencias (opcional)
composer update
```

**Tiempo esperado**: 2-5 minutos según velocidad de conexión.

---

### PASO 3: Configurar la Base de Datos

#### 3.1: Crear la Base de Datos

```bash
# Conéctate a MySQL/MariaDB
mysql -u root -p

# En el prompt de MySQL, crea la base de datos
CREATE DATABASE prueba_tecnica_exito CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Crea un usuario (recomendado no usar root en producción)
CREATE USER 'drupal_user'@'localhost' IDENTIFIED BY 'drupal_password_segura';
GRANT ALL PRIVILEGES ON prueba_tecnica_exito.* TO 'drupal_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3.2: Configurar `settings.php`

Drupal requiere una configuración de base de datos en su archivo de configuración.

```bash
# Navega al directorio de configuración de Drupal
cd web/sites/default

# Copia el archivo de ejemplo (si existe)
cp settings.example.php settings.php
cp default.settings.php settings.php  # Alternativa si no existe settings.example.php

# Abre settings.php y busca la sección de base de datos
nano settings.php  # O usa tu editor preferido (vim, code, etc.)
```

**Localiza esta sección en `settings.php` y reemplaza los valores:**

```php
$databases['default']['default'] = array(
  'driver' => 'mysql',
  'database' => 'prueba_tecnica_exito',
  'username' => 'drupal_user',
  'password' => 'drupal_password_segura',
  'host' => '127.0.0.1',
  'port' => '3306',
  'prefix' => '',
  'collation' => 'utf8mb4_unicode_ci',
);

/**
 * Trusted Host Configuration.
 * 
 * Drupal validates the Host header to match trusted patterns.
 * For local development, add this to prevent 'Untrusted Host' errors:
 */
$settings['trusted_host_patterns'] = [
  '^localhost$',
  '^127\.0\.0\.1$',
  '^PruebaTecnicaExito\.local$',
];

/**
 * Hash salt for security.
 */
$settings['hash_salt'] = 'FZIRtlQ6d7mV_92kcbwXKTmV3hen5g6FxTg4jfeuX5xHqDScilN6b_8Ef6D8SpgebA7yARIFxw';
```

#### 3.3: Crear Directorio `files` y Configurar Permisos

```bash
# Desde la raíz del proyecto
mkdir -p web/sites/default/files
chmod -R 755 web/sites/default/files
chmod 644 web/sites/default/settings.php
```

---

### PASO 4: Ejecutar el Instalador de Drupal

Hay dos opciones: **Web basada** o **CLI con Drush**.

#### Opción A: Instalación vía Drush (Recomendado ⭐)

```bash
# Desde la raíz del proyecto
drush si drupal --db-url=mysql://drupal_user:drupal_password_segura@127.0.0.1:3306/prueba_tecnica_exito --site-name="Prueba Técnica Exito" --account-name=admin --account-pass=AdminPassword123 --locale=es -y
```

**Parámetros explicados:**
- `drupal`: Perfil de instalación estándar
- `--db-url`: Cadena de conexión a la BD
- `--site-name`: Nombre del sitio mostrado en la interfaz
- `--account-name`: Usuario administrador
- `--account-pass`: Contraseña del admin
- `--locale=es`: Instalar en español
- `-y`: Confirmar sin preguntar

#### Opción B: Instalación vía Web

1. Abre en tu navegador: `http://localhost/PruebaTecnicaExito/web`
2. Sigue el asistente de instalación
3. En el paso "Database", ingresa:
   - **Driver**: MySQL
   - **Host**: `127.0.0.1`
   - **Database**: `prueba_tecnica_exito`
   - **Username**: `drupal_user`
   - **Password**: `drupal_password_segura`
4. Completa los pasos restantes

---

### PASO 5: Habilitar el Módulo Custom Events

Una vez completada la instalación de Drupal, habilita el módulo personalizado:

```bash
# Habilitar el módulo custom_events
drush en custom_events -y

# Alternativamente, vía interfaz web:
# Navega a: /admin/modules → Busca "Custom Events" → Marca la casilla → Instalar
```

**Verificar activación:**

```bash
drush pml | grep custom_events
```

---

### PASO 6: Limpiar Caché

```bash
# Reconstruir caché de Drupal
drush cr

# O si prefieres una limpieza más profunda
drush cache:clear all
```

---

### PASO 7: Configurar Permisos de Usuario

Asigna permisos a los roles de usuario para que puedan registrarse en eventos:

```bash
# Permitir a usuarios autenticados registrarse en eventos
drush role:perm:add authenticated 'register for events'

# Permitir a administradores gestionar eventos
drush role:perm:add administrator 'administer events'
```

**Verificar permisos vía web:**
1. Ve a: `/admin/people/permissions`
2. Busca "Custom Events"
3. Marca las casillas según el rol

---

### PASO 8: Configurar el Tema Móvil Éxito

#### 8.1: Habilitar el Tema

```bash
# Establecer movilexito_theme como tema predeterminado
drush tset movilexito_theme -y

# Verificar tema activo
drush tst
```

**Vía interfaz web:**
1. Ve a: `/admin/appearance`
2. Busca "Móvil Éxito Theme"
3. Haz clic en "Establecer como predeterminado"

#### 8.2: Verificar Configuración del Tema

```bash
# Limpiar caché para asegurar que se cargan los estilos correctos
drush cr

# Invalidar caché de navegador (Ctrl+Shift+Supr en la mayoría de navegadores)
```

#### 8.3: Validar que los Estilos Sean Correctos

1. Navega a: `http://localhost/PruebaTecnicaExito/web`
2. Abre las DevTools del navegador (F12)
3. Verifica que se cargan estos archivos CSS:
   - `movilexito_theme/css/style.css`
   - `custom_events/css/custom-events.css`
4. Confirma que los colores son:
   - **Púrpura principal**: `#2e008b`
   - **Amarillo destacado**: `#ffea00`
   - **Tipografía**: Inter y Poppins desde Google Fonts

---

## ⚙️ Configuración Adicional (Opcional pero Recomendada)

### Habilitar módulos de desarrollo

```bash
# Habilitar Devel (para debugging)
drush en devel -y

# Habilitar RestUI (para explorar APIs REST)
drush en rest -y devel_generate -y
```

### Generar eventos de prueba

```bash
# Generar 10 eventos de prueba
drush genc custom_event 10
```

### Configurar SMTP (para envío de emails)

Si planeas enviar notificaciones por email:

1. Instala el módulo **Mail System**: `drush en mailsystem -y`
2. Instala **SMTP**: `drush en smtp -y`
3. Configura credenciales en: `/admin/config/system/smtp`

---

## 🏗️ Decisiones Técnicas y Arquitectura

### 1️⃣ Service Pattern para Consumo de API

**Decisión**: Crear `CountryService` como servicio Symfony inyectable.

**Rationale**:
- ✅ Lógica de negocio separada del controlador
- ✅ Reutilizable en múltiples contextos (formas, APIs REST, etc.)
- ✅ Fácil de testear en aislamiento
- ✅ Permite cambiar fuente de datos sin reescribir código cliente

**Implementación**:
```php
// src/Service/CountryService.php
$countries = $this->countryService->getCountries();
// Internamente: API REST → parsing JSON → caché
```

---

### 2️⃣ Entity API en lugar de SQL Crudo

**Decisión**: Usar `ContentEntity` para registros de inscripción en lugar de tabla personalizada.

**Rationale**:
- ✅ Integración completa con permiso system de Drupal
- ✅ Validadores Symfony a nivel de entidad
- ✅ Historial automático (created, changed timestamps)
- ✅ Soporta futuros módulos de ampliación (views, rest, etc.)
- ✅ Queries abstrayendo detalles de BD

**Ejemplo**:
```php
// En lugar de:
// SELECT * FROM custom_event_registrations WHERE uid = ? AND event_id = ?

// Usamos:
$storage->getQuery()
  ->condition('uid', $uid)
  ->condition('event_id', $eventId)
  ->accessCheck(FALSE)
  ->execute();
```

---

### 3️⃣ Estrategia de Caché Inteligente

**Decisión**: Caché de 24 horas con fallback a caché obsoleto para lista de países.

**Rationale**:
- ✅ Reduce latencia en forms (no esperar API en cada carga)
- ✅ Resiliente a fallos de API (sirve datos viejos antes de error)
- ✅ Cumple SLA de disponibilidad
- ✅ Reduce ancho de banda a restcountries.com

**Flujo**:
```
1. ¿Hay caché válido? → Devolver inmediatamente
2. ¿API responde? → Almacenar en caché + devolver
3. ¿API falla? → Devolver caché expirado silenciosamente
4. ¿No hay caché? → Lanzar excepción
```

**Configuración**:
```php
const CACHE_TTL = 86400; // 24 horas en segundos
$this->cache->set($id, $data, time() + self::CACHE_TTL);
```

---

### 4️⃣ CSRF Protection en Endpoints AJAX

**Decisión**: Token fresco de `/session/token` + header `X-CSRF-Token`.

**Rationale**:
- ✅ Previene CSRF attacks en formularios cross-site
- ✅ Token vinculado a URL específica (scoped token)
- ✅ Validación server-side con `CsrfTokenGenerator`
- ✅ Compatible con cualquier navegador

**Flujo** (Javascript):
```javascript
// 1. Obtener token
const token = await fetch('/session/token').then(r => r.text());

// 2. Enviar POST con token en header
fetch('/events/1/register', {
  method: 'POST',
  headers: { 'X-CSRF-Token': token }
});
```

---

### 5️⃣ AJAX sin Framework (Vanilla JS)

**Decisión**: `fetch()` API + `Drupal.behaviors` pattern (sin jQuery ni framework).

**Rationale**:
- ✅ Zero frameworks overhead (moderne navegadores soportan fetch)
- ✅ Compatible con Drupal AJAX API (attach/detach hooks)
- ✅ Fácil debugging (console.log)
- ✅ Comportamiento correcto en AJAX/Views dinámicas

**Patrón Drupal.behaviors**:
```javascript
Drupal.behaviors.customEventsRegistration = {
  attach: function(context) {
    // Ejecutado al cargar página y después de AJAX
  },
  detach: function(context) {
    // Limpiar listeners si es necesario
  }
};
```

---

### 6️⃣ Validador Symfony para Integridad de Datos

**Decisión**: Constraint validator `UniqueEventRegistration` a nivel de entidad.

**Rationale**:
- ✅ Previene duplicados incluso si BD falla
- ✅ Mensaje de error claro en formularios
- ✅ Validated por Drupal antes de guardar
- ✅ Re-usable en APIs REST

**Implementación**:
```php
// Anotación en Entity
@ContentEntityType(
  constraints = {
    "UniqueEventRegistration" = {}
  }
)

// Validador personalizado
class UniqueEventRegistrationConstraintValidator extends ConstraintValidator {
  public function validate($entity, Constraint $constraint) {
    if ($this->isDuplicate($entity)) {
      $this->context->addViolation($constraint->message);
    }
  }
}
```

---

## 🧪 Credenciales de Prueba (Después de Instalación)

Una vez completada la instalación, puedes acceder con:

### Usuario Administrador Predeterminado

```
URL: http://localhost/PruebaTecnicaExito/web

Usuario: admin
Contraseña: AdminPassword123
```

### Crear Usuarios de Prueba Adicionales

Para probar el flujo de registro/inscripción:

```bash
# Crear usuario de prueba
drush ucrt testuser --password="TestPassword123" --mail="test@example.com"

# Asignar rol autenticado (otomático)
drush urol authenticated testuser
```

**Acceder como usuario de prueba:**
1. Ve a `http://localhost/PruebaTecnicaExito/web/user/logout`
2. Haz clic en "Crear cuenta"
3. O inicia sesión con:
   - **Usuario**: `testuser`
   - **Contraseña**: `TestPassword123`

---

## 📊 Flujo de Prueba Recomendado

### 1. Crear un Evento (como Admin)

```
1. Ve a: /admin/content/events/add
2. Rellena:
   - Título: "Conferencia de Tecnología 2026"
   - Descripción: "Una conferencia inspiradora..."
   - País: Selecciona "Colombia"
   - Fecha: Elige una fecha futura
   - Publicado: ✓ Marca
3. Haz clic en "Save"
```

### 2. Ver Evento en Página Pública

```
1. ve a: /events
2. Debes ver la card del evento creado
3. Contador de inscritos debe mostrar "0 registrados"
```

### 3. Registrarse en Evento (como Usuario Autenticado)

```
1. Si no estás autenticado, haz clic en "Inicia sesión para registrarte"
2. Inicia sesión con testuser/TestPassword123
3. Haz clic en "Registrarse"
4. Verás un spinner mientras se procesa
5. Botón cambia a "Ya registrado ✓" (verde/amarillo)
6. Contador de inscritos aumenta a "1 registrado"
7. Toast verde confirma: "Registro exitoso"
```

### 4. Intentar Registrarse de Nuevo (Validación Antiduplicado)

```
1. Intenta hacer clic en "Ya registrado ✓"
2. Verás un toast rojo: "Ya estás registrado para este evento"
3. El botón permanece deshabilitado
```

### 5. Ver Registros en Admin

```
1. Ve a: /admin/content/event-registrations
2. Debes ver tu registro listado
3. Filtrable por usuario y evento
```

---

## 🔧 Troubleshooting (Solución de Problemas)

### ❌ Error: "SQLSTATE[HY000]: General error: 1030"

**Causa**: Base de datos no configured correctamente.

**Solución**:
```bash
# Verificar conexión a MySQL
mysql -u drupal_user -p'drupal_password_segura' -h 127.0.0.1 -e "USE prueba_tecnica_exito; SHOW TABLES;"

# Reconstruir BD desde Drupal
drush updb -y
drush cr
```

---

### ❌ Error: "The directory sites/default/files either does not exist or is not writable"

**Causa**: Permisos insuficientes en carpeta de archivos.

**Solución**:
```bash
# Otorgar permisos correctos
chmod -R 755 web/sites/default/files
chmod 644 web/sites/default/settings.php

# En Windows (si usas Git Bash):
attrib -R web\sites\default\files /S /D
```

---

### ❌ Error: "API de países no responde"

**Síntoma**: Formulario de crear evento lento o el select de país está vacío.

**Causa**: 
- API REST Countries inaccesible
- Proxy/firewall bloqueando conexiones HTTPS
- Timeout en curl

**Solución**:
```bash
# Verificar conectividad a API
curl -I "https://restcountries.com/v3.1/all?fields=name"

# Debe responder con 200 OK

# Limpiar caché de países para forzar recarga
drush cache:clear 'custom_events:countries'

# Habilitar logging para debugging
drush vget logger.channel.custom_events
```

**Fallback automático**: 
- El módulo sirve caché expirado (si existe) sin fallar
- Si no hay caché previo, muestra error describiendo el problema

---

### ❌ Error: "Access denied" en `/events`

**Causa**: Permisos de usuario mal configurados.

**Solución**:
```bash
# Verificar permisos asignados
drush role:perm:list authenticated

# Debe incluir 'access content'
# Debe incluir 'register for events' si quieres inscripciones

# Asignar permisos
drush role:perm:add authenticated 'access content'
drush role:perm:add authenticated 'register for events'

# Limpiar caché
drush cr
```

---

### ❌ Error: "404 - Página no encontrada" en `/events`

**Causa**: Módulo no habilitado o caché corrupto.

**Solución**:
```bash
# Verificar módulo habilitado
drush pml | grep custom_events

# Si no aparece, habilitar:
drush en custom_events -y

# Reconstruir rutas
drush route:rebuild
drush cr
```

---

### ❌ Error: "El formulario CSRF token es inválido"

**Síntoma**: Botón de registro devuelve error 403 Forbidden.

**Causa**:
- Token expirado
- Navegador bloqueando cookies
- Múltiples pestañas con sesiones conflictivas

**Solución**:
```bash
# Limpiar cookies del navegador (F12 → Application → Cookies → Delete)
# O usar navegación privada/incógnita

# En servidor, verificar CSRF token config:
drush cget user.settings

# Verificar que session.cookie_secure = FALSE (desarrollo)
# en settings.php
```

---

### ❌ Error: "Estilos CSS no cargan"

**Síntoma**: Página ve sin colores, sin tipografía correcta.

**Causa**:
- Caché de navegador
- Tema no habilitado
- Rutas incorrectas a archivos

**Solución**:
```bash
# Limpiar caché Drupal
drush cache:clear theme
drush cr

# Abrir DevTools y limpiar caché navegador
# (Ctrl+Shift+Supr o F12 → Application → Clear Site Data)

# Verificar que tema está activo
drush tst

# Si no, establecer:
drush tset movilexito_theme -y
```

---

### ❌ Error: "Fatal error: Class not found"

**Causa**: Composer dependencies no instaladas correctamente.

**Solución**:
```bash
# Reinstalar dependencias
rm -rf vendor composer.lock
composer install

# Regenerar autoloader
composer dump-autoload -o

# Limpiar y reconstruir caché Drupal
drush cache:clear all
drush rebuild-scripts
drush cr
```

---

### ❌ Error: "MySQL: Too many connections"

**Síntoma**: Después de muchos requests, base de datos rechaza conexiones.

**Causa**: Pool de conexiones agotado en MySQL.

**Solución**:
```bash
# En settings.php, reducir max_connections:
$databases['default']['default']['max_connections'] = 5;

# O aumentar en MySQL (my.cnf):
[mysqld]
max_connections = 100

# Reiniciar MySQL:
# sudo systemctl restart mysql
# O en Windows: Services → MySQL → Restart
```

---

## 📞 Soporte Técnico

En caso de problemas no cubiertos en este documento:

1. **Revisa los logs de Drupal**:
   ```bash
   drush watchdog:tail --limit=20
   ```

2. **Habilita modo de debug** (development/settings.php):
   ```php
   $config['system.logging']['error_level'] = 'verbose';
   ```

3. **Consulta la documentación oficial**:
   - [Drupal 10 Docs](https://www.drupal.org/docs/10)
   - [REST Countries API](https://restcountries.com/)
   - [Drupal Entity API](https://www.drupal.org/docs/8/api/entity-api)

---

## 📝 Notas Importantes

- ⚠️ **Seguridad en Producción**: Cambia ALL las contraseñas por defecto antes de desplegar.
- ⚠️ **HTTPS Obligatorio**: En producción, fuerza HTTPS en `settings.php`:
  ```php
  // Fuerza HTTPS
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
  }
  ```
- ⚠️ **Límite de Rate**: En producción, implementa rate limiting en `/events/{id}/register`.
- ✅ **Backups**: Haz backup regular de BD antes de actualizaciones.

---

## 📄 Estructura del Proyecto

```
PruebaTecnicaExito/
├── composer.json              # Dependencias PHP
├── composer.lock             # Lock file (no editar)
├── web/                      # Raíz web de Drupal
│   ├── index.php
│   ├── core/                 # Drupal core
│   ├── modules/
│   │   └── custom/
│   │       └── custom_events/       # 👈 Módulo personalizado
│   ├── themes/
│   │   └── custom/
│   │       └── movilexito_theme/    # 👈 Tema personalizado
│   └── sites/
│       └── default/
│           ├── settings.php   # ⚠️ Editar para BD
│           └── files/         # ⚠️ Permisos 755
├── vendor/                   # Dependencias (Composer)
├── README.md                # Documentación principal
└── README_INSTALACION.md    # ← TÚ ESTÁS AQUÍ
```

---

## ✅ Checklist de Instalación

- [ ] Requisitos previos verificados (PHP 8.1+, MySQL, Composer)
- [ ] Repositorio clonado
- [ ] Dependencias instaladas (`composer install`)
- [ ] Base de datos creada
- [ ] `settings.php` configurado con credenciales BD
- [ ] Drupal instalado (`drush si ...`)
- [ ] Módulo `custom_events` habilitado
- [ ] Tema `movilexito_theme` establecido
- [ ] Permisos asignados (register for events, administer events)
- [ ] Caché reconstruido (`drush cr`)
- [ ] `/events` accesible en navegador
- [ ] Evento de prueba creado
- [ ] Inscripción funcionando sin reload

---

## 🎉 ¡Está Listo!

Tu instalación de **Prueba Técnica - Desarrollador Full Stack** está completa.

**Accede aquí**: `http://localhost/PruebaTecnicaExito/web`

---

**Última actualización**: 29 de Marzo de 2026  
**Versión**: 1.2.0  
**Compatible con**: Drupal 10.0+, PHP 8.4+, MySQL 9.1+
