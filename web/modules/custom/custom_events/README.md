# web/modules/custom/custom_events/README.md

# Custom Events — Drupal 10 Module

---

## 1. Overview

**Custom Events** is a fully custom Drupal 10 module that provides end-to-end event management with AJAX-driven user registrations. It is built entirely on Drupal's native APIs — no contributed dependencies required.

### Features

- **Event management** — Create, edit, publish/unpublish, and delete events through a dedicated admin UI at `/admin/content/events`.
- **Country selection** — The event form populates a country `<select>` from the [REST Countries API](https://restcountries.com/v3.1/all?fields=name), cached locally for 24 hours with automatic stale-cache fallback.
- **User registration** — Authenticated users register for events via a single AJAX POST request; no page reload required.
- **CSRF protection** — Every registration request fetches a fresh Drupal session token (`/session/token`) and passes it in the `X-CSRF-Token` header, validated server-side by `CsrfTokenGenerator`.
- **Cascade deletion** — Deleting an Event automatically removes all associated `EventRegistration` records via `hook_ENTITY_TYPE_predelete()`.
- **Unique constraint** — A Symfony Validator plugin (`UniqueEventRegistration`) prevents duplicate registrations at the entity level.
- **Brand-aligned frontend** — Responsive card grid with Móvil Éxito's primary red (`#E30613`), Inter/Poppins typography, micro-animations, and inline loading/success/error states.
- **Accessible markup** — Semantic HTML5, ARIA live regions on count badges and toasts, `role="status"` / `role="alert"`.

---

## 2. Requirements

| Requirement | Version |
|---|---|
| Drupal | `^10.0` |
| PHP | `^8.1` |
| Drush | `^11` or `^12` |
| Composer | `^2` |

No contributed modules are required. The module uses only Drupal core services:

- `http_client` (Guzzle) — REST Countries API calls
- `cache.default` — 24-hour country list cache
- `entity_type.manager` — entity storage and queries
- `csrf_token` — registration endpoint protection

---

## 3. Installation

### 3.1 Copy the module

```bash
# From your Drupal project root
cp -r /path/to/custom_events web/modules/custom/custom_events
```

Or if the module is version-controlled as part of the project it is already in place at:

```
web/modules/custom/custom_events/
```

### 3.2 Enable the module

```bash
drush en custom_events -y
```

This runs `hook_install()`, which creates the `custom_event_registrations` database table and displays a status message.

### 3.3 Clear caches

```bash
drush cr
```

### 3.4 Set permissions

Navigate to **Admin → People → Permissions** (`/admin/people/permissions`) or use Drush:

```bash
# Grant authenticated users the ability to register for events
drush role:perm:add authenticated 'register for events'

# Grant an administrator role full event management
drush role:perm:add administrator 'administer events'
```

| Permission | Recommended role |
|---|---|
| `register for events` | Authenticated user |
| `administer events` | Administrator / Event Manager |

---

## 4. Usage

### 4.1 Creating events

1. Go to **`/admin/content/events/add`** (requires `administer events` permission).
2. Fill in:
   - **Title** (required, max 255 characters)
   - **Description** (rich text)
   - **Country** — populated from the REST Countries API dropdown
   - **Event Date & Time** — must be a future date when creating
   - **Published** checkbox
3. Save. The event becomes visible at `/events` once published.

### 4.2 Viewing and registering

- Browse to **`/events`** (requires `access content` permission — available to all users including anonymous).
- Authenticated users with `register for events` permission see a **"Registrarse"** button on each card.
- Clicking the button:
  1. Fetches a CSRF token from `/session/token`.
  2. POSTs to `/events/{id}/register`.
  3. On success: button changes to **"Ya registrado ✓"** and the registration count updates in place.
  4. On error: a dismissing toast message appears below the button.

### 4.3 Managing registrations

Administrators can view all registrations at **`/admin/content/event-registrations`**.

---

## 5. Technical Decisions

| Decision | Choice | Rationale |
|---|---|---|
| **Data model** | Custom `ContentEntity` (`custom_event`) instead of a Node bundle | Avoids coupling to the Node system and its field/display overhead. Gives full control over entity keys, routes, handlers, and admin UI without polluting content types. |
| **Registration storage** | Custom `ContentEntity` (`event_registration`) instead of the Flag module | No contributed dependency. The entity carries structured data (uid, event_id, created) queryable via `EntityQuery`. A `UniqueEventRegistration` Symfony Constraint enforces integrity at the entity layer rather than at the DB layer alone. |
| **Service layer** | `CountryService` + `EventRegistrationService` as Symfony services | Separates business logic from controllers and forms. Services are fully injectable, testable in isolation, and reusable across routes, forms, and future REST resources. |
| **Cache API** | `cache.default` with CID `custom_events:countries`, TTL 24 h, stale fallback | The REST Countries API is external and slow. A 24-hour cache avoids a network call on every form render. On API failure the service serves stale data silently before throwing, preserving UX stability. |
| **Entity queries** | `EntityTypeManager::getStorage()->getQuery()` | Keeps queries database-agnostic and honours entity access. Avoids raw SQL for all read paths except the cascade-delete in `hook_ENTITY_TYPE_predelete()` which requires atomicity. |
| **Frontend** | Vanilla JS `fetch()` inside a `Drupal.behaviors` closure | No framework overhead. The Behaviors API ensures AJAX-loaded content (e.g. Views AJAX, BigPipe) re-attaches listeners correctly via `attach`/`detach`. `data-ce-bound` guards against duplicate listeners. |
| **CSRF validation** | Fresh token from `/session/token` + `X-CSRF-Token` header | Drupal's built-in `CsrfTokenGenerator` validates the token tied to the scoped URL (`custom_events_register_{id}`), preventing cross-site request forgery on the unauthenticated-accessible POST endpoint. |

---

## 6. Technical Decisions

This section explains the architectural choices made in this module.

### Service Pattern for External API Integration

**Decision**: Create `CountryService` as an injectable Symfony service.

**Rationale**:
- Separates API consumption logic from controllers and forms
- Reusable across multiple contexts (forms, REST endpoints, commands)
- Testable in isolation with mocked HTTP client
- Easy to extend (e.g., switch to different API source)
- Supports dependency injection and compile-time validation

**Caching Strategy**:
- 24-hour cache of country list from REST Countries API
- Automatic fallback to stale cache if API fails (graceful degradation)
- Prevents repeated network calls during form renders
- Reduces external dependency latency

---

### Entity API Instead of Raw SQL

**Decision**: Use custom `ContentEntity` for event registration instead of a custom table.

**Rationale**:
- Proper integration with Drupal's access control system
- Automatic timestamps (created, changed)
- Validator plugins work at entity level (UniqueEventRegistration)
- Future-proof: compatible with Views, REST API, RevisionUI, etc.
- Queries use EntityQuery API (database-agnostic)
- No raw SQL = no injection vulnerabilities

**Example**:
```php
// ✅ Entity Query (safe, database-agnostic)
$storage->getQuery()
  ->condition('event_id', $eventId)
  ->condition('uid', $uid)
  ->accessCheck(FALSE)
  ->range(0, 1)
  ->execute();

// ❌ Raw SQL (avoided in this module)
// SELECT * FROM custom_event_registrations WHERE event_id = ? AND uid = ?
```

---

### Symfony Validator Plugins

**Decision**: Enforce "unique registration" via a Symfony Constraint Validator, not just database-level checks.

**Rationale**:
- Constraint validation happens before entity save
- Prevents duplicate insertions even if DB constraint fails
- Produces user-friendly error messages in forms
- Works across all entity creation paths (forms, API, scripts)
- Standards-compliant (PSR-12, Symfony best practices)

---

### CSRF Protection in AJAX Endpoints

**Decision**: Fresh CSRF token from `/session/token` + `X-CSRF-Token` header validation.

**Rationale**:
- Drupal's `CsrfTokenGenerator` validates scoped URL tokens
- Token is scoped to `custom_events_register_{event_id}` to prevent token reuse
- Prevents cross-site request forgery attacks
- Works across all modern browsers without extra configuration
- Compatible with Drupal's session system

---

### Vanilla JavaScript with Drupal Behaviors

**Decision**: `fetch()` API + `Drupal.behaviors` pattern (no jQuery, no framework).

**Rationale**:
- Modern browsers support `fetch()` natively
- Zero framework overhead
- Compatible with dynamic Drupal AJAX (Views AJAX, BigPipe) via `attach`/`detach`
- Easy to debug (browser console, network tab)
- No build step required

---

## 7. Uninstall

```bash
drush pmu custom_events -y
drush cr
```

`hook_uninstall()` removes the module configuration. The `custom_event_registrations` table is dropped automatically by Drupal's schema system when the module is uninstalled.

> **Warning:** Uninstalling the module permanently deletes all Event entities, all EventRegistration entities, and the associated database tables. Export or back up any data you need before uninstalling.

---

## 8. Troubleshooting

### Issue: "Unable to retrieve country list" error

**Symptom**: Event form shows an error message; country select is empty.

**Cause**: REST Countries API is unreachable or timing out.

**Solution**:
```bash
# Test API connectivity
curl -I https://restcountries.com/v3.1/all?fields=name

# Should return HTTP 200

# Clear the country cache to force reload
drush cache:clear 'custom_events:countries'

# Check module logs
drush watchdog:show --filter=custom_events
```

**Expected behavior**: If the API is unavailable but a cache exists (even if expired), the module serves the stale cache silently. Only throws an error if the API fails AND there is no cache.

---

### Issue: "User already registered" on duplicate attempt

**Symptom**: Button shows "Already registered" even after clicking once.

**Cause**: This is intentional behavior (UniqueEventRegistration constraint prevents duplicates).

**Solution**: None needed — this is correct behavior. Users should not register twice.

---

### Issue: CSRF token validation fails (403 Forbidden)

**Symptom**: Registration button returns a 403 error.

**Cause**: 
- Token is expired (max session lifetime)
- Browser cookies are disabled
- Multiple tabs with conflicting sessions

**Solution**:
```bash
# Clear browser cookies and try again
# Or use private/incognito browsing mode

# Check Drupal session settings
drush cget user.settings

# Token should be tied to URL custom_events_register_{event_id}
```

---

### Issue: Styles not loading correctly

**Symptom**: Event cards appear without colors, wrong fonts, broken layout.

**Cause**:
- Browser caching old CSS
- Drupal cache not cleared
- Theme not properly enabled

**Solution**:
```bash
# Clear all caches
drush cache:clear all

# Clear browser cache (Ctrl+Shift+Delete or Cmd+Shift+Delete)

# Verify theme is enabled
drush tst

# Verify library is attached
drush eval "echo json_encode(\Drupal::service('library.discovery')->getLibraries('custom_events'));"
```

---

### Issue: "Access Denied" when viewing `/events`

**Symptom**: 403 error page when navigating to `/events`.

**Cause**: User lacks `access content` permission.

**Solution**:
```bash
# Grant permission to role
drush role:perm:add authenticated 'access content'

# Or via admin UI: /admin/people/permissions
# Search for "Access published content" and check the role

# Rebuild permissions cache
drush cache:clear user_permissions
drush cr
```

---

### Issue: "404" page at `/events`

**Symptom**: Page not found error.

**Cause**: Module not enabled or routes not rebuilt.

**Solution**:
```bash
# Verify module is enabled
drush pml | grep custom_events

# If not, enable it
drush en custom_events -y

# Rebuild routes
drush route:rebuild

# Clear cache
drush cr
```

---

## File Structure

```
custom_events/
├── css/
│   └── custom-events.css
├── js/
│   └── custom-events.js
├── src/
│   ├── Controller/
│   │   └── EventController.php
│   ├── Entity/
│   │   ├── Event.php
│   │   └── EventRegistration.php
│   ├── EventListBuilder.php
│   ├── Form/
│   │   ├── EventDeleteForm.php
│   │   ├── EventForm.php
│   │   └── EventRegistrationForm.php
│   ├── Plugin/
│   │   └── Validation/
│   │       └── Constraint/
│   │           ├── UniqueEventRegistrationConstraint.php
│   │           └── UniqueEventRegistrationConstraintValidator.php
│   └── Service/
│       ├── CountryService.php
│       └── EventRegistrationService.php
├── templates/
│   ├── event-card.html.twig
│   └── event-list.html.twig
├── custom_events.info.yml
├── custom_events.install
├── custom_events.libraries.yml
├── custom_events.module
├── custom_events.permissions.yml
├── custom_events.routing.yml
└── custom_events.services.yml
```
