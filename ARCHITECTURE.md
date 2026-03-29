# 🏗️ ARQUITECTURA Y DECISIONES TÉCNICAS

## 🎯 Visión General

Este documento describe las decisiones arquitectónicas clave tomadas en el desarrollo del módulo **custom_events** y el tema **movilexito_theme**.

---

## 📐 Diagrama de Arquitectura

```
┌─────────────────────────────────────────────────────────┐
│                    CAPA PRESENTACIÓN                     │
│  (HTML/CSS/JS + Drupal Theme System)                    │
├─────────────────────────────────────────────────────────┤
│  event-list.html.twig  +  event-card.html.twig          │
│  movilexito_theme/css/style.css                         │
│  custom-events.js (Vanilla JS + fetch AJAX)            │
└────────────────────────┬────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────┐
│              CAPA APLICACIÓN/CONTROLADOR                 │
│       (Drupal Controller + Form API)                    │
├─────────────────────────────────────────────────────────┤
│  EventController.php                                    │
│  ├─ listEvents() → Queries published events             │
│  └─ registerForEvent() → AJAX endpoint (JSON)           │
│                                                         │
│  EventForm.php                                          │
│  ├─ form() → Inyecta CountryService                    │
│  ├─ validateForm()                                      │
│  └─ save()                                              │
│                                                         │
│  EventDeleteForm.php                                    │
│  EventRegistrationForm.php                              │
└────────────────────────┬────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────┐
│                 CAPA SERVICIOS                           │
│       (Symfony Service + Dependency Injection)          │
├─────────────────────────────────────────────────────────┤
│  EventRegistrationService                               │
│  ├─ register(eventId) → crea EventRegistration          │
│  ├─ isRegistered(eventId, uid) → boolean               │
│  └─ getCount(eventId) → integer                         │
│                                                         │
│  CountryService                                         │
│  ├─ getCountries() → array['name' => 'name']           │
│  ├─ Caché 24h                                           │
│  ├─ Fallback a caché estadía                            │
│  └─ Guzzle HTTP Client                                  │
└────────────────────────┬────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────┐
│              CAPA PERSISTENCIA/DATOS                     │
│         (Drupal Entity API + Database)                  │
├─────────────────────────────────────────────────────────┤
│  Event Entity                                           │
│  ├─ Tabla: custom_event                                 │
│  ├─ Campos: id, uuid, title, description, country      │
│  └─ status, uid, created, changed                       │
│                                                         │
│  EventRegistration Entity                               │
│  ├─ Tabla: event_registration                           │
│  ├─ Campos: id, event_id, uid, created                  │
│  ├─ Validador: UniqueEventRegistration                  │
│  └─ Índices: (event_id, uid) UNIQUE                     │
│                                                         │
│  Cache (keyed by: custom_events:countries)              │
│  ├─ TTL: 86400 segundos (24 horas)                      │
│  └─ Fallback: Permite caché expirado                    │
└────────────────────────┬────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────┐
│            SERVICIOS EXTERNOS                            │
├─────────────────────────────────────────────────────────┤
│  REST Countries API                                     │
│  ├─ GET https://restcountries.com/v3.1/all?fields=name │
│  └─ Responde con lista de países                        │
│                                                         │
│  MySQL/MariaDB Database                                 │
│  └─ Almacena eventos y registros                        │
└─────────────────────────────────────────────────────────┘
```

---

## 🔄 Flujo de Datos: Crear Evento

```
Usuario Admin → Accede a /events/add
                    ↓
            EventForm::form() cargada
                    ↓
            CountryService::getCountries() llamada
                    ↓
         ¿Caché válido activo? → SÍ → Devolver países cacheados
                    ↓ NO
         GuzzleHttp::request() → https://restcountries.com/v3.1/all
                    ↓
         ¿API responde 200? → SÍ → Parsear JSON, almacenar en caché
                    ↓ NO (timeout/error)
         ¿Caché estadío existe? → SÍ → Devolver sin error
                    ↓ NO
         RuntimeException("Unable to retrieve...")
                    ↓
            form['country'] = ['options' => $countries] rellenado
                    ↓
            Usuario selecciona país, rellena formulario
                    ↓
            EventForm::validateForm()
                    ↓
            EventForm::save() → EntityTypeManager::save()
                    ↓
            Base de datos INSERT en tabla custom_event
                    ↓
            Usuario redirigido a /events (evento visible si published=1)
```

---

## 🔄 Flujo de Datos: Registrarse en Evento (AJAX)

```
Usuario Autenticado → Ve /events (GET)
                    ↓
        EventController::listEvents() cargada
                    ↓
        EntityQuery('custom_event')
        · Filter: status = 1 (published)
        · Order: event_date DESC
        · Limit: 20 (paginado)
                    ↓
        Para cada evento:
        · EventRegistrationService::getCount(event_id)
        · ¿Usuario registrado?: isRegistered(event_id, uid)
        · Generar CSRF token scoped
                    ↓
        Renderizar event-list.html.twig + event-card.html.twig
                    ↓
        Incluir JS: custom-events.js
                    ↓
        Drupal.behaviors.customEventsRegistration.attach()
        · Vinculan listeners en botones .js-register-btn
        · Guardan data-event-id, data-register-url, data-csrf-token
                    ↓
        Usuario hace clic en botón "Registrarse"
                    ↓
        handleRegisterClick() ejecuta:
        · Obtener token CSRF de data attribute
        · fetch('/events/{id}/register', {
            method: 'POST',
            headers: { 'X-CSRF-Token': token }
          })
                    ↓
        POST /events/{event_id}/register → EventController
                    ↓
        EventController::registerForEvent():
        · ¿Usuario autenticado? → 403 si no
        · ¿Evento existe? → 404 si no
        · ¿Token CSRF válido? → 403 si no
        · Llamar EventRegistrationService::register(event_id)
                    ↓
        EventRegistrationService::register():
        · ¿Registro existente (uid, event_id)? → Throw RuntimeException
        · Crear entidad EventRegistration
        · Validador UniqueEventRegistration chequea duplicados
        · EntityStorage::save()
                    ↓
        Base de datos INSERT en tabla event_registration
                    ↓
        JSON Response: {"status":"success", "count":15, "message":"..."}
                    ↓
        JavaScript recibe respuesta:
        · setRegistered(btn) → button.textContent = "Ya registrado ✓"
        · updateCountBadge(eventId, 15)
        · showToast(toastEl, "Registro exitoso", "success")
                    ↓
        Toast desaparece después de 5 segundos
        UI completamente actualizada SIN page reload ✨
```

---

## 🔐 Flujo de Seguridad

### CSRF Token Validation

```
1. Cliente obtiene token:
   GET /session/token → "abc123def456"

2. Token se genera scopeado:
   Scoped ID: "custom_events_register_15"
   (vinculado a evento específico)

3. Cliente envía POST con token:
   POST /events/15/register
   Headers: { 'X-CSRF-Token': 'abc123def456' }

4. Servidor valida:
   CsrfTokenGenerator::validate(token, 'custom_events_register_15')

5. Si válido → continúa
   Si inválido → 403 Forbidden
```

### Access Control (Permissions)

```
GET /events
├─ Required: 'access content'
├─ Anonymous: ✓ (pueden ver eventos)
└─ Authenticated: ✓ (pueden ver + registrarse)

POST /events/{id}/register
├─ Required: 'register for events'
├─ Anonymous: ✗ (403 Forbidden)
├─ Authenticated: ✓
└─ Admin: ✓

/admin/content/events
├─ Required: 'administer events'
├─ Anonymous: ✗
├─ Authenticated: ✗
└─ Admin: ✓

/admin/content/event-registrations
├─ Required: 'administer events'
└─ Vista de todos los registros (filtrable)
```

---

## 💾 Schema de Base de Datos

### Tabla: `custom_event`

Generada automáticamente por Drupal Entity system.

```sql
CREATE TABLE `custom_event` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(128) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `uid` int UNSIGNED NOT NULL DEFAULT 0,
  `created` int NOT NULL DEFAULT 0,
  `changed` int NOT NULL DEFAULT 0,
  `description` longtext,
  `country` varchar(255) NOT NULL DEFAULT '',
  `event_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabla: `event_registration`

```sql
CREATE TABLE `event_registration` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(128) NOT NULL DEFAULT '',
  `event_id` int UNSIGNED NOT NULL,
  `uid` int UNSIGNED NOT NULL,
  `created` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `unique_registration` (`event_id`, `uid`),
  KEY `event_id` (`event_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabla: `cache_default` (Caché de Países)

Drupal usa tabla de caché única para todos los servicios.

```sql
-- Caché de países (CID = 'custom_events:countries')
-- Renovado cada 24 horas (expire = UNIX_TIMESTAMP + 86400)
INSERT INTO cache_default 
  (cid, data, created, expire, tags) 
VALUES 
  ('custom_events:countries', 
   serialize(['Colombia' => 'Colombia', ...]), 
   1711737600,
   1711824000,
   'custom_events_countries');
```

---

## 🎨 Decisiones de Frontend

### Por qué NO usamos un Framework JS

| Framework | Ventaja | Desventaja |
|-----------|---------|-----------|
| **React** | Componentes reutilizables | +100KB minified, build step, learning curve |
| **Vue.js** | Reactivity, directivas | +50KB, build step, jQuery como alternativa mejor |
| **jQuery** | Sencillo, DOM manipulation | Deprecated en Drupal 9+, no soporta modernos navegadores |
| **Vanilla JS** ✅ | 0 overhead, Drupal compatible | Requiere más código, pero es moderno |

**Solución**: Vanilla JS con `fetch()` API + `Drupal.behaviors` pattern.

---

### Por qué `Drupal.behaviors`

**Escenario**: Un desarrollador carga eventos vía Views AJAX.

```javascript
// Primer page load
Drupal.behaviors.customEventsRegistration.attach(context);
// → Vincula listeners en todos los botones

// Views AJAX carga 3 eventos más
Drupal.behaviors.customEventsRegistration.attach(context);
// → Vincula listeners en los nuevos botones (sin duplicarse)
// → Gracias a data-ce-bound guard
```

Sin `Drupal.behaviors`, tendríamos múltiples listeners en el mismo botón = bug.

---

## 📦 Decisiones de Dependencias

### ✅ Dependencias Incluidas (Drupal Core)

```php
// En custom_events.services.yml
services:
  custom_events.country_service:
    class: Drupal\custom_events\Service\CountryService
    arguments:
      - '@http_client'          // ← core/guzzle
      - '@cache.default'        // ← core/cache
      - '@logger.channel.custom_events'  // ← core/logging

  custom_events.event_registration_service:
    class: Drupal\custom_events\Service\EventRegistrationService
    arguments:
      - '@entity_type.manager'  // ← core/entity
      - '@current_user'         // ← core/user
```

**Beneficio**: Cero dependencias externas = cero actualizaciones, cero seguridad issues, máxima compatibilidad.

---

## 🗂️ Decisiones de Organización de Código

```
custom_events/
├── src/
│   ├── Controller/        # Controladores HTTP
│   ├── Service/           # Lógica de negocio reutilizable
│   ├── Form/              # Formularios Drupal
│   ├── Entity/            # Definiciones de entidad
│   ├── Plugin/Validation/ # Validadores Symfony
│   └── EventListBuilder.php # Listar entidades en admin
├── templates/             # Plantillas Twig
├── css/                   # Estilos SCSS compilado a CSS
├── js/                    # JavaScript vanila
├── images/                # Imágenes SVG
├── custom_events.routing.yml
├── custom_events.permissions.yml
├── custom_events.services.yml
├── custom_events.module
├── custom_events.install
└── README.md
```

**Beneficio**: Separación clara de responsabilidades, fácil de navegar, escalable.

---

## 🚀 Performance & Caching Strategy

### Cache Levels

```
1. HTTP Browser Cache
   ├─ static/css/style.css → 1 año (content-hash in filename)
   ├─ static/js/custom-events.js → 1 año
   └─ images/*.svg → 1 año

2. Drupal Render Cache
   ├─ event_list render array con:
   │  ├─ tags: ['custom_event_list', 'custom_event_entity:*']
   │  ├─ contexts: ['user', 'url.query_args']
   │  └─ max-age: 3600 (1 hora para anónimo)
   └─ Invalidado cuando: evento guardado/eliminado

3. Drupal Data Cache
   ├─ custom_events:countries
   │  ├─ TTL: 86400 (24 horas)
   │  └─ Fallback: caché estadío si API falla
   └─ Invalidado cuando: drush cache:clear 'custom_events:countries'
```

### Cache Invalidation

```php
// Cuando un evento es guardado
$event->save();
// Drupal automáticamente invalida:
// - tags: ['custom_event_list', 'custom_event_entity:123']
// - Views usando eventos

// Cascade delete cuando evento es borrado
hook_ENTITY_TYPE_predelete():
  // Deletes all registrations for this event
  // No new cache issues

// Manual clear
drush cache:clear 'custom_events:countries'
```

---

## 📊 Comparativa de Alternativas Consideradas

### Registros: Entity vs Node vs Table

| Opción | Ventaja | Desventaja | ✅ Elegida |
|--------|---------|-----------|----------|
| **Custom Entity** | Permisos nativos, validadores, extensible | Más complejo | ✅ |
| **Node** | Familiar, fields revisables | Overhead innecesario, pollutes content | ❌ |
| **SQL Table** | Rápido, simple | Sin permisos, sin validadores, SQL injection risk | ❌ |

---

### País: API vs Hardcoded vs Config

| Opción | Ventaja | Desventaja | ✅ Elegida |
|--------|---------|-----------|----------|
| **REST API** | Siempre actualizado, oficial | Requiere internet, latency | ✅ |
| **Hardcoded** | Rápido | Actualización manual, desactualizado | ❌ |
| **Config** | Flexible | Mantenimiento manual | ❌ |

---

### CSRF: Drupal Token vs Custom vs None

| Opción | Ventaja | Desventaja | ✅ Elegida |
|--------|---------|-----------|----------|
| **Drupal CSRF** | Integrado, validado, scoped | Requiere fetch token | ✅ |
| **Custom Token** | Control total | Reinventar la rueda, bugs | ❌ |
| **None** | Simple | VULNERABLE a CSRF | ❌ |

---

## 🔮 Consideraciones Futuras

Si este módulo creciera, podrían ser agregadas:

```
├─ REST API Endpoints
│  ├─ GET /api/events
│  ├─ POST /api/events/{id}/register
│  └─ DELETE /api/events/{id}/unregister

├─ GraphQL Endpoint
│  └─ query { events { id title registeredCount } }

├─ JSON:API (standard Drupal)
│  └─ /jsonapi/event?filter[status]=1

├─ Drush Commands
│  ├─ drush event:create
│  ├─ drush event:publish
│  └─ drush registration:export

├─ Webhooks
│  └─ On event created → POST to external system

├─ Queue API (para operaciones async)
│  └─ Envío de emails masivo cuando evento comienzar

├─ Views Integration
│  └─ Vistas admin de eventos con filtros avanzados

└─ Search API Integration
   └─ Búsqueda full-text indexada de eventos
```

---

## 📚 Referencias y Recursos

- [Drupal 10 Entity API Docs](https://www.drupal.org/docs/drupal-apis/entity-api)
- [Drupal Form API](https://www.drupal.org/docs/drupal-apis/form-api)
- [Symfony Validator Component](https://symfony.com/doc/current/validation.html)
- [REST Countries API Docs](https://restcountries.com/)
- [Web.dev - CSRF Prevention](https://web.dev/same-site-cookies-explained/)
- [Web.dev - Fetch API](https://web.dev/introduction-to-fetch/)

---

**Última actualización**: 29 de Marzo de 2026  
**Versión de Drupal**: 10.0+  
**Versión de PHP**: 8.1+
