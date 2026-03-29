# 🚀 GUÍA DE INICIO RÁPIDO (5 MINUTOS)

## Si ya tienes el proyecto instalado en tu máquina...

---

## 1️⃣ Activar el Sitio Localmente

```bash
# Navega al proyecto
cd C:\wamp64\www\PruebaTecnicaExito

# Inicia el servidor de desarrollo
drush serve

# O si usas WAMP: abre en navegador http://localhost/PruebaTecnicaExito/web
```

---

## 2️⃣ Acceder como Administrador

```
URL: http://localhost/PruebaTecnicaExito/web
Usuario: admin
Contraseña: AdminPassword123
```

---

## 3️⃣ Crear tu Primer Evento

**Opción 1: Vía interfaz web**
1. Ve a: `/admin/content/events/add`
2. Llena:
   - 📝 **Título**: "Mi Primer Evento"
   - 📝 **Descripción**: "Una evento increíble"
   - 🌍 **País**: Colombia
   - 📅 **Fecha**: Mañana a las 2 PM
   - ✓ **Publicado**: Marca la casilla
3. Haz clic en "Save"

**Opción 2: Vía Drush (más rápido)**
```bash
# Generar 3 eventos de prueba automáticamente
drush genc custom_event 3
```

---

## 4️⃣ Ver Eventos en la Página Pública

```
Navega a: http://localhost/PruebaTecnicaExito/web/events

Deberías ver:
✅ Carousel con imágenes (arriba)
✅ Tarjetas de eventos con:
   - Título y descripción
   - País y fecha
   - Contador de inscritos
   - Botón "Registrarse" (si estás autenticado)
```

---

## 5️⃣ Registrarse en un Evento

```
1. Inicia sesión (arriba a la derecha)
2. Vuelve a /events
3. Haz clic en "Registrarse"
4. ¡Sin recarga de página! ✨
5. Botón cambia a "Ya registrado ✓"
6. Contador aumenta a "1 registrado"
```

---

## 📱 Comprobar que Todo Funciona

```bash
# Ver logs en tiempo real
drush watchdog:tail

# Listar eventos creados
drush sql:query "SELECT id, title FROM custom_event LIMIT 5"

# Listar registros
drush sql:query "SELECT * FROM event_registration LIMIT 5"

# Limpiar caché si cambias algo
drush cr
```

---

## 🎨 Verificar Estilos Correctos

Abre tu navegador en `/events` y verifica:

- ✅ **Colores**: Púrpura (#2e008b) y Amarillo (#ffea00)
- ✅ **Tipografía**: Inter y Poppins elegantes
- ✅ **Responsive**: Prueba en phone (F12 → Toggle Device)
- ✅ **Animaciones**: Botones con hover suave

---

## 🆘 Si Algo Falla

### Estados comunes:

**❌ "No hay eventos"**
```bash
drush genc custom_event 5  # Genera 5 eventos
drush cr                    # Limpia caché
```

**❌ "API de países no funciona"**
```bash
# Verificar conexión
curl https://restcountries.com/v3.1/all

# Si falla, el módulo usa caché fallback automáticamente
```

**❌ "No veo estilos CSS"**
```bash
# Limpiar caché de navegador: Ctrl+Shift+Supr
drush cache:clear theme
drush cr
```

**❌ "No puedo registrarme"**
- Inicia sesión primero
- Ve a `/admin/people/permissions`
- Marca "Register for events" para "Authenticated user"

---

## 📊 Datos de Prueba Disponibles

```bash
# Crear usuario de prueba
drush ucrt prueba --password="Test123!" --mail="prueba@test.com"

# Crear 10 eventos de prueba
drush genc custom_event 10

# Crear 5 registros de prueba
drush sql:query "INSERT INTO event_registration (event_id, uid, created) 
                 VALUES (1, 1, UNIX_TIMESTAMP()), 
                        (1, 2, UNIX_TIMESTAMP()),
                        (2, 3, UNIX_TIMESTAMP());"
```

---

## 🔗 Links Útiles

| Página | URL |
|--------|-----|
| 🏠 **Home** | `/` |
| 📅 **Eventos** | `/events` |
| 👤 **Mi Perfil** | `/user` |
| 🔐 **Login** | `/user/login` |
| ✍️ **Registro** | `/user/register` |
| 🛠️ **Admin** | `/admin` |
| 📋 **Eventos (Admin)** | `/admin/content/events` |
| 📊 **Registros (Admin)** | `/admin/content/event-registrations` |
| 🔑 **Permisos** | `/admin/people/permissions` |

---

## 💡 Tips Pro

```bash
# Ver estado del módulo
drush status-report

# Habilitar/deshabilitar módulos rápidamente
drush en custom_events              # Activar
drush pmu custom_events             # Desactivar

# Ejecutar comandos SQL rápidamente
drush sql:cli  # Abre terminal MySQL interactivo

# Exportar configuración (si modificas)
drush cex

# Importar configuración
drush cim
```

---

## ⏱️ Checklist de 5 Minutos

- [ ] Proyecto descargado
- [ ] `drush serve` ejecutándose
- [ ] Accedes a `/` sin errores
- [ ] Puedes loguear como `admin`
- [ ] Ves `/events` con al menos 1 evento
- [ ] Puedes registrarte sin refrescar página
- [ ] El contador dinámico funciona

**Si todo está ✅ → ¡Listo para usar!**

---

🎯 **Para documentación completa**: Ver `README_INSTALACION.md`  
🐛 **¿Problemas?**: Ver "Troubleshooting" en documentación completa
