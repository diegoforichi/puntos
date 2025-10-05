# Arquitectura Técnica - Sistema Multitenant

## Fecha: 2024-12-19

## 🏗️ Arquitectura General

### Stack Tecnológico
- **Backend**: PHP 8.1+ + Laravel 10
- **Frontend**: Bootstrap 5 + JavaScript Vanilla
- **Base de Datos**: MySQL (producción) + SQLite (desarrollo)
- **Servidor Web**: Apache/Nginx con mod_rewrite
- **Dependencias**: Composer (todo incluido en `vendor/`)

### Flujo de Datos
```
eFactura → Webhook Único → Laravel API → MySQL (por tenant) → Interfaz Web
                ↓
        Portal Autoconsulta ← Cliente (solo documento)
                ↓
        WhatsApp Service ← Notificaciones
```

## 🏢 Sistema Multitenant

### Estrategia: Database per Tenant
```
puntos_system/
├── database/
│   ├── puntos_main.sql          # Base principal con tenants
│   ├── tenant_empresa1.sql      # Base tenant 1
│   ├── tenant_empresa2.sql      # Base tenant 2
│   └── tenant_empresaN.sql      # Base tenant N
└── storage/
    ├── backups/
    │   ├── tenant_empresa1/
    │   └── tenant_empresa2/
    └── logs/
        ├── webhook.log
        └── tenant.log
```

### Identificación de Tenants
- **Por RUT**: Cada empresa identificada por su RUT
- **Mapping**: Tabla `tenants` mapea RUT → database_name
- **URLs**: `dominio.com/{tenant_slug}` (ej: `dominio.com/empresa1`)
- **Webhook**: RUT en payload identifica tenant destino

## 🗄️ Estructura de Base de Datos

### Base Principal (`puntos_main`)
```sql
-- Tabla maestra de tenants
CREATE TABLE tenants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rut VARCHAR(20) UNIQUE NOT NULL,           -- RUT de la empresa
    name VARCHAR(255) NOT NULL,                -- Nombre de la empresa
    slug VARCHAR(100) UNIQUE NOT NULL,         -- Para URLs (empresa1)
    database_name VARCHAR(100) UNIQUE NOT NULL, -- Nombre de la BD
    api_key VARCHAR(255) UNIQUE NOT NULL,      -- API Key para webhook
    status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Configuración global del sistema
CREATE TABLE system_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    key_name VARCHAR(100) UNIQUE NOT NULL,
    key_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Base por Tenant (`tenant_empresaX`)
```sql
-- Clientes del tenant
CREATE TABLE clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    documento VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(255),                        -- NUEVO: para notificaciones
    puntos_acumulados INT DEFAULT 0,
    ultima_actividad TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,               -- NUEVO: activar/desactivar
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_documento (documento),
    INDEX idx_puntos (puntos_acumulados),
    INDEX idx_activo (activo)
);

-- Configuración del tenant
CREATE TABLE configuracion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    puntos_por_pesos INT DEFAULT 100,
    vencimiento_dias INT DEFAULT 365,
    cotizacion DECIMAL(10,2) DEFAULT 40.00,
    nombre_empresa VARCHAR(255),
    logo_url VARCHAR(500),                     -- NUEVO: logo personalizado
    colores_tema JSON,                         -- NUEVO: personalización UI
    notificaciones_activas BOOLEAN DEFAULT TRUE, -- NUEVO: activar WhatsApp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Usuarios del tenant
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255),                        -- NUEVO: email del usuario
    password VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'supervisor', 'operador', 'general') DEFAULT 'general', -- NUEVO: más roles
    permisos JSON,                             -- NUEVO: permisos granulares
    ultimo_acceso TIMESTAMP,                   -- NUEVO: tracking de acceso
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo)
);

-- Facturas (webhook)
CREATE TABLE facturas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_factura VARCHAR(100) UNIQUE NOT NULL,
    cliente_documento VARCHAR(50),
    cliente_nombre VARCHAR(255),               -- NUEVO: nombre del cliente
    monto_total DECIMAL(10,2),
    puntos_generados INT,
    promocion_aplicada VARCHAR(100),           -- NUEVO: promoción usada
    fecha_factura TIMESTAMP,
    procesada_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_cliente (cliente_documento),
    INDEX idx_fecha (fecha_factura),
    INDEX idx_procesada (procesada_at)
);

-- Puntos canjeados
CREATE TABLE puntos_canjeados (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,
    usuario_id INT,                            -- NUEVO: quién procesó el canje
    puntos INT NOT NULL,
    descuento_aplicado DECIMAL(5,2) DEFAULT 0, -- NUEVO: % descuento
    fecha_canje TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notas TEXT,                                -- NUEVO: observaciones
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_fecha (fecha_canje)
);

-- Puntos vencidos
CREATE TABLE puntos_vencidos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,
    puntos INT NOT NULL,
    fecha_vencimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(100) DEFAULT 'vencimiento_automatico',
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_fecha (fecha_vencimiento)
);

-- NUEVO: Promociones y campañas
CREATE TABLE promociones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    tipo ENUM('multiplicador', 'puntos_extra', 'descuento_canje') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,              -- Multiplicador, puntos o % descuento
    fecha_inicio DATE,
    fecha_fin DATE,
    activa BOOLEAN DEFAULT TRUE,
    condiciones JSON,                          -- Reglas de aplicación
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_fechas (fecha_inicio, fecha_fin),
    INDEX idx_activa (activa)
);

-- NUEVO: Historial de actividades
CREATE TABLE actividades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,              -- 'canje_puntos', 'login', etc.
    entidad VARCHAR(50),                       -- 'cliente', 'configuracion', etc.
    entidad_id INT,                            -- ID del registro afectado
    datos_anteriores JSON,                     -- Estado antes del cambio
    datos_nuevos JSON,                         -- Estado después del cambio
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (created_at)
);

-- NUEVO: Notificaciones enviadas
CREATE TABLE notificaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,
    tipo VARCHAR(50) NOT NULL,                 -- 'canje', 'vencimiento', 'promocion'
    canal VARCHAR(20) DEFAULT 'whatsapp',      -- 'whatsapp', 'email', 'sms'
    mensaje TEXT,
    estado ENUM('pendiente', 'enviado', 'fallido') DEFAULT 'pendiente',
    enviado_at TIMESTAMP NULL,
    error_mensaje TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_tipo (tipo),
    INDEX idx_estado (estado)
);
```

## 🔗 API y Webhooks

### Webhook Único
```php
// Endpoint único para todos los tenants
POST /api/webhook/ingest

// Headers requeridos
Authorization: Bearer {api_key_del_tenant}
Content-Type: application/json

// Payload
{
    "rut_emisor": "210010020030",              // Identifica el tenant
    "numero_factura": "F001-12345",
    "cliente_documento": "12345678",
    "cliente_nombre": "Juan Pérez",
    "cliente_telefono": "099123456",           // Opcional
    "monto_total": 5000.00,
    "fecha_factura": "2024-12-19T10:30:00Z",
    "items": [                                 // Opcional: detalle de productos
        {
            "descripcion": "Producto A",
            "cantidad": 2,
            "precio_unitario": 2500.00
        }
    ]
}

// Respuesta exitosa
{
    "status": "success",
    "tenant": "empresa1",
    "puntos_generados": 50,
    "puntos_acumulados": 150,
    "promocion_aplicada": null,
    "mensaje": "Puntos procesados correctamente"
}
```

### API para Portal de Autoconsulta
```php
// Consulta pública de puntos (sin autenticación)
GET /api/{tenant}/consulta/{documento}

// Respuesta
{
    "status": "success",
    "cliente": {
        "nombre": "Juan Pérez",
        "puntos_acumulados": 150,
        "ultima_actividad": "2024-12-19T10:30:00Z"
    },
    "historial_canjes": [
        {
            "fecha": "2024-12-01T15:20:00Z",
            "puntos": 100
        }
    ]
}
```

### API Interna (Autenticada)
```php
// Autenticación
POST /api/{tenant}/auth/login
{
    "username": "admin",
    "password": "password"
}

// Gestión de clientes
GET    /api/{tenant}/clientes
POST   /api/{tenant}/clientes
PUT    /api/{tenant}/clientes/{id}
DELETE /api/{tenant}/clientes/{id}

// Canje de puntos
POST   /api/{tenant}/clientes/{id}/canjear
{
    "puntos": 100,
    "descuento": 0,
    "notas": "Canje regular"
}

// Configuración
GET    /api/{tenant}/configuracion
PUT    /api/{tenant}/configuracion

// Reportes
GET    /api/{tenant}/reportes/dashboard
GET    /api/{tenant}/reportes/clientes
GET    /api/{tenant}/reportes/canjes
```

## 🔒 Seguridad

### Autenticación por Tenant
- **Laravel Sanctum**: Tokens de API por usuario
- **Middleware**: Validación de tenant en cada request
- **Sesiones**: Por tenant, aisladas entre empresas
- **Rate Limiting**: Por IP y por tenant

### Webhook Security
- **API Key**: Bearer token único por tenant
- **Validación**: RUT + API Key matching en tabla tenants
- **Rate Limiting**: 100 requests/minuto por tenant
- **Logging**: Todas las peticiones registradas
- **IP Whitelist**: Opcional, por tenant

### Protección de Datos
- **Encriptación**: Contraseñas con bcrypt
- **Datos sensibles**: Encriptados en BD (teléfonos, emails)
- **Logs**: Sin información sensible
- **Backup**: Cifrado con contraseña

## 🌐 Routing y URLs

### Estructura de URLs
```php
// Portal principal (neutro)
GET  /                              → Página de selección de empresa

// Portal por tenant
GET  /{tenant}                      → Dashboard del tenant
GET  /{tenant}/login                → Login del tenant
GET  /{tenant}/configuracion        → Configuración (solo admin)
GET  /{tenant}/clientes             → Gestión de clientes
GET  /{tenant}/reportes             → Reportes y estadísticas
GET  /{tenant}/usuarios             → Gestión de usuarios (solo admin)

// Portal público de consulta
GET  /{tenant}/consulta             → Formulario de consulta pública
POST /{tenant}/consulta             → Procesar consulta

// API
POST /api/webhook/ingest            → Webhook único
GET  /api/{tenant}/consulta/{doc}   → API pública de consulta
POST /api/{tenant}/auth/login       → API de autenticación
GET  /api/{tenant}/*                → APIs internas (autenticadas)
```

### Middleware Stack
```php
// Para rutas web del tenant
Route::group(['prefix' => '{tenant}', 'middleware' => ['tenant', 'web']], function() {
    // Rutas del tenant
});

// Para APIs del tenant
Route::group(['prefix' => 'api/{tenant}', 'middleware' => ['tenant', 'api']], function() {
    // APIs autenticadas
    Route::group(['middleware' => 'auth:sanctum'], function() {
        // Rutas protegidas
    });
});

// Webhook (sin tenant en URL, identificado por payload)
Route::post('/api/webhook/ingest', [WebhookController::class, 'ingest'])
    ->middleware(['throttle:webhook']);
```

## 📱 Frontend y UI

### Arquitectura Frontend
- **Blade Templates**: Para renderizado server-side
- **Bootstrap 5**: Framework CSS responsive
- **JavaScript Vanilla**: Sin dependencias externas
- **AJAX**: Para operaciones dinámicas
- **Local Assets**: CSS/JS incluidos en el proyecto

### Personalización por Tenant
```php
// Configuración de tema por tenant
{
    "colores_tema": {
        "primario": "#007bff",
        "secundario": "#6c757d",
        "fondo": "#ffffff"
    },
    "logo_url": "/storage/tenant1/logo.png",
    "nombre_empresa": "Mi Empresa S.A."
}
```

### Componentes Reutilizables
- **Layout base**: Header, sidebar, footer
- **Tablas**: Con paginación, búsqueda, ordenamiento
- **Modales**: Confirmación, formularios
- **Formularios**: Validación client-side y server-side
- **Dashboards**: Widgets de estadísticas

## 🔄 Procesos y Jobs

### Tareas Programadas (Cron)
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Eliminar puntos vencidos (todos los tenants)
    $schedule->command('puntos:eliminar-vencidos')
             ->daily()
             ->at('02:00');
    
    // Backup de bases de datos
    $schedule->command('backup:databases')
             ->daily()
             ->at('03:00');
    
    // Enviar notificaciones pendientes
    $schedule->command('notificaciones:procesar')
             ->everyFiveMinutes();
    
    // Generar reportes automáticos
    $schedule->command('reportes:generar-automaticos')
             ->weekly()
             ->sundays()
             ->at('04:00');
}
```

### Queue Jobs (Opcional)
- **Notificaciones**: Envío asíncrono de WhatsApp
- **Reportes**: Generación de reportes pesados
- **Backup**: Respaldo de bases de datos grandes
- **Importación**: Migración de datos masiva
