# 🔐 PROPUESTA: Panel SuperAdmin

**Fecha:** 30 de Septiembre de 2025  
**Estado:** Propuesta para aprobación

---

## 🎯 OBJETIVO

Crear un panel exclusivo para el **SuperAdmin** que permita gestionar la configuración global del sistema (credenciales SMTP, WhatsApp, gestión de tenants).

---

## 📋 FUNCIONALIDADES PROPUESTAS

### 1. **Configuración de Email (SMTP)**
Formulario para configurar el servidor de correo:
- Host (ej: `smtp.gmail.com`)
- Puerto (ej: `587`)
- Usuario (ej: `sistema@miempresa.com`)
- Contraseña (campo protegido, con confirmación)
- Encriptación (TLS/SSL)
- Botón "Probar Conexión" (envía email de prueba)

**Almacenamiento:** `system_config` tabla (MySQL), key: `email_smtp`

---

### 2. **Configuración de WhatsApp**
Formulario para configurar el servicio de WhatsApp:
- Endpoint/URL del proveedor (ej: `https://api.whatsapp.com/send`)
- Token de API (campo protegido)
- Número de origen (ej: `+59899123456`)
- Botón "Probar Conexión" (envía mensaje de prueba)

**Almacenamiento:** `system_config` tabla (MySQL), key: `whatsapp_config`

---

### 3. **Gestión de Tenants**
Tabla con listado de todos los comercios:
- RUT
- Nombre Comercial
- API Key (oculta, botón "Regenerar")
- Estado (Activo/Inactivo)
- Fecha de creación
- Acciones:
  - Ver detalles
  - Editar datos básicos
  - Regenerar API Key
  - Desactivar/Activar

**Funcionalidad adicional:**
- Botón "Crear Nuevo Tenant"
- Formulario modal con: RUT, Nombre, Email, Teléfono
- Genera automáticamente la base SQLite y el API Key

---

### 4. **Monitoreo Global**
Dashboard exclusivo para SuperAdmin:
- Total de tenants activos
- Total de facturas procesadas (todos los tenants)
- Total de puntos en circulación
- Últimos webhooks recibidos (tabla `webhook_inbox_global`)
- Tenants con más actividad (top 5)

---

### 5. **Webhook Inbox Global**
Vista de todos los webhooks recibidos:
- Fecha/Hora
- Tenant (RUT)
- Estado (exitoso/fallido)
- Mensaje de error (si aplica)
- Payload JSON (expandible)
- Filtros por tenant, estado, fecha

---

## 🏗️ ESTRUCTURA PROPUESTA

### **Rutas**
```php
// Solo accesible con rol 'superadmin'
Route::prefix('superadmin')->middleware(['auth.global', 'role:superadmin'])->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard']);
    
    // Configuración global
    Route::get('/config', [SuperAdminController::class, 'config']);
    Route::post('/config/email', [SuperAdminController::class, 'saveEmailConfig']);
    Route::post('/config/whatsapp', [SuperAdminController::class, 'saveWhatsAppConfig']);
    Route::post('/config/test-email', [SuperAdminController::class, 'testEmail']);
    Route::post('/config/test-whatsapp', [SuperAdminController::class, 'testWhatsApp']);
    
    // Gestión de tenants
    Route::get('/tenants', [SuperAdminController::class, 'tenants']);
    Route::post('/tenants', [SuperAdminController::class, 'createTenant']);
    Route::put('/tenants/{id}', [SuperAdminController::class, 'updateTenant']);
    Route::post('/tenants/{id}/regenerate-key', [SuperAdminController::class, 'regenerateApiKey']);
    Route::post('/tenants/{id}/toggle', [SuperAdminController::class, 'toggleTenant']);
    
    // Monitoreo
    Route::get('/webhooks', [SuperAdminController::class, 'webhooks']);
});
```

### **Middleware Nuevo**
`auth.global` - Autentica usuarios SuperAdmin (tabla `users` de MySQL principal)

### **Controlador**
`app/Http/Controllers/SuperAdminController.php`

### **Vistas**
```
resources/views/superadmin/
├── layout.blade.php          # Layout exclusivo para SuperAdmin
├── dashboard.blade.php        # Dashboard global
├── config.blade.php           # Configuración SMTP/WhatsApp
├── tenants/
│   ├── index.blade.php        # Listado de tenants
│   └── create.blade.php       # Formulario crear tenant
└── webhooks.blade.php         # Webhook Inbox Global
```

---

## 🔐 SEGURIDAD

### **Autenticación**
- SuperAdmin se autentica en `/superadmin/login` (diferente a tenant login)
- Sesión independiente de las sesiones de tenants
- Timeout de sesión: 120 minutos

### **Protección de Credenciales**
- Contraseñas y tokens se guardan **encriptados** con `Crypt::encryptString()`
- Al mostrar en formularios, se muestran como `****` y solo se actualizan si el usuario ingresa un valor nuevo

### **Logs de Auditoría**
- Toda acción del SuperAdmin se registra en una tabla `admin_logs`:
  - Usuario
  - Acción (ej: "Modificó config SMTP", "Creó tenant X")
  - IP
  - Timestamp

---

## 🎨 UI PROPUESTO

### **Sidebar SuperAdmin**
```
┌─────────────────────────┐
│ 🔐 SuperAdmin Panel     │
├─────────────────────────┤
│ 📊 Dashboard Global     │
│ ⚙️  Configuración       │
│ 🏢 Tenants              │
│ 📥 Webhooks             │
│ 📜 Logs de Auditoría    │
│ 🚪 Cerrar Sesión        │
└─────────────────────────┘
```

### **Dashboard Global**
```
┌──────────────┬──────────────┬──────────────┬──────────────┐
│ Tenants      │ Facturas     │ Puntos       │ Webhooks     │
│ Activos: 5   │ Mes: 1,234   │ Total: 45k   │ Hoy: 89      │
└──────────────┴──────────────┴──────────────┴──────────────┘

📊 Tenants con Más Actividad (últimos 7 días)
┌────────────────────────────────────────────┬───────────┐
│ Comercio A (RUT: 123456789)                │ 456 fact. │
│ Comercio B (RUT: 987654321)                │ 234 fact. │
│ Comercio C (RUT: 555555555)                │ 123 fact. │
└────────────────────────────────────────────┴───────────┘

📥 Últimos Webhooks Recibidos
┌─────────────┬─────────────┬────────────┬──────────┐
│ Fecha/Hora  │ Tenant      │ Estado     │ Acción   │
├─────────────┼─────────────┼────────────┼──────────┤
│ 18:45:23    │ 123456789   │ ✅ OK      │ Ver      │
│ 18:44:12    │ 987654321   │ ✅ OK      │ Ver      │
│ 18:43:01    │ 555555555   │ ❌ Error   │ Ver      │
└─────────────┴─────────────┴────────────┴──────────┘
```

### **Configuración Global**
```
┌─────────────────────────────────────────────────────┐
│ ⚙️  Configuración Global del Sistema                │
├─────────────────────────────────────────────────────┤
│                                                     │
│ 📧 Configuración SMTP                               │
│ ┌─────────────────────────────────────────────┐   │
│ │ Host:     [smtp.gmail.com          ]        │   │
│ │ Puerto:   [587                     ]        │   │
│ │ Usuario:  [sistema@empresa.com     ]        │   │
│ │ Contraseña: [**********************]        │   │
│ │ Encriptación: [⦿ TLS  ○ SSL]               │   │
│ │                                             │   │
│ │ [Probar Conexión]  [Guardar]               │   │
│ └─────────────────────────────────────────────┘   │
│                                                     │
│ 📱 Configuración WhatsApp                           │
│ ┌─────────────────────────────────────────────┐   │
│ │ Endpoint: [https://api.whatsapp.com]       │   │
│ │ Token:    [**********************]          │   │
│ │ Número:   [+59899123456           ]         │   │
│ │                                             │   │
│ │ [Probar Conexión]  [Guardar]               │   │
│ └─────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────┘
```

---

## 📊 IMPACTO EN LA BASE DE DATOS

### **Tabla `users` (MySQL principal)**
Ya existe. Se usará para SuperAdmin:
```sql
INSERT INTO users (name, email, password, role) VALUES
('SuperAdmin', 'admin@sistema.com', bcrypt('admin123'), 'superadmin');
```

### **Tabla `system_config`**
Ya existe. Se agregarán nuevas keys:
- `email_smtp` → JSON con config SMTP
- `whatsapp_config` → JSON con config WhatsApp

### **Tabla `admin_logs` (nueva)**
```sql
CREATE TABLE admin_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    accion VARCHAR(255) NOT NULL,
    descripcion TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## ⏱️ ESTIMACIÓN DE TIEMPO

- **Middleware y Autenticación:** 1 hora
- **Controlador y Lógica:** 2 horas
- **Vistas (4 archivos):** 2 horas
- **Testing Manual:** 1 hora
- **Total:** ~6 horas

---

## ✅ ALTERNATIVAS CONSIDERADAS

### **Opción 1: Panel SuperAdmin Completo** (Propuesta Actual)
**Ventajas:**
- UI clara y dedicada
- Seguridad centralizada
- Fácil de expandir

**Desventajas:**
- Más tiempo de desarrollo

---

### **Opción 2: Variables de Entorno (.env)**
**Ventajas:**
- Implementación rápida (5 min)

**Desventajas:**
- No editable desde UI
- Requiere acceso al servidor
- No escala bien

---

### **Opción 3: Configuración en Panel de Tenant**
**Ventajas:**
- Reutiliza UI existente

**Desventajas:**
- Confunde roles (¿quién configura qué?)
- Inseguro (admin de tenant no debería ver credenciales globales)

---

## 🎯 DECISIÓN

**Recomiendo la Opción 1: Panel SuperAdmin Completo**

**Razones:**
1. **Claridad:** Separación clara de responsabilidades
2. **Seguridad:** Credenciales protegidas y auditadas
3. **Escalabilidad:** Fácil agregar más funcionalidades
4. **UX:** Interfaz dedicada y profesional

---

## ❓ PREGUNTAS PARA EL USUARIO

Antes de implementar, necesito confirmar:

1. **¿Te parece correcto este enfoque?**
2. **¿Agregarías o quitarías alguna funcionalidad?**
3. **¿Implementamos esto ahora o lo dejamos para la Fase 3?**

Si lo apruebas, procedo con la implementación en las próximas 6 horas de trabajo.

---

**Autor:** Asistente IA  
**Para aprobación de:** Usuario

