# 📘 Documentación Técnica - Sistema de Puntos
**Versión:** 1.0  
**Fecha:** 30 de Septiembre de 2025  
**Stack:** Laravel 10 + MySQL + SQLite + Bootstrap 5

---

## 📑 Índice

1. [Arquitectura del Sistema](#arquitectura-del-sistema)
2. [Stack Tecnológico](#stack-tecnológico)
3. [Estructura de Directorios](#estructura-de-directorios)
4. [Base de Datos](#base-de-datos)
5. [Modelos Eloquent](#modelos-eloquent)
6. [Middleware](#middleware)
7. [Controladores](#controladores)
8. [Rutas](#rutas)
9. [Vistas y Frontend](#vistas-y-frontend)
10. [Webhook y Adapters](#webhook-y-adapters)
11. [Servicios](#servicios)
12. [Comandos Artisan](#comandos-artisan)
13. [Patrones de Diseño](#patrones-de-diseño)
14. [Seguridad](#seguridad)
15. [Testing](#testing)
16. [API Reference](#api-reference)

---

## 🏗️ Arquitectura del Sistema

### Arquitectura Multi-tenant

El sistema implementa una arquitectura **Database per Tenant**:

```
┌─────────────────────────────────────────────────────┐
│                   MySQL (Main DB)                    │
│  ┌──────────┐  ┌──────────────┐  ┌───────────────┐ │
│  │ tenants  │  │system_config │  │webhook_inbox_ │ │
│  │          │  │              │  │    global     │ │
│  └──────────┘  └──────────────┘  └───────────────┘ │
└─────────────────────────────────────────────────────┘
                        │
        ┌───────────────┴───────────────┐
        │                               │
┌───────▼──────────┐           ┌────────▼─────────┐
│ tenant_1.sqlite  │           │ tenant_2.sqlite  │
│ ┌──────────────┐ │           │ ┌──────────────┐ │
│ │  clientes    │ │           │ │  clientes    │ │
│ │  facturas    │ │           │ │  facturas    │ │
│ │  usuarios    │ │           │ │  usuarios    │ │
│ │  ...         │ │           │ │  ...         │ │
│ └──────────────┘ │           │ └──────────────┘ │
└──────────────────┘           └──────────────────┘
```

### Flujo de Datos

```
┌──────────────┐
│  eFactura    │
│   System     │
└──────┬───────┘
       │ POST /api/webhook/ingest
       │ (JSON + API Key)
       ▼
┌──────────────────┐
│ WebhookController│
│  - Valida API Key│
│  - Identifica RUT│
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│ EfacturaAdapter  │
│  - Normaliza JSON│
│  - StandardDTO   │
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│  PuntosService   │
│  - Calcula puntos│
│  - Aplica promo  │
│  - Guarda datos  │
└──────────────────┘
```

### Ventajas de esta arquitectura

✅ **Aislamiento total** de datos entre tenants  
✅ **Escalabilidad** horizontal fácil  
✅ **Backup independiente** por cliente  
✅ **Migrations** independientes  
✅ **No hay JOINS** entre tenants  

---

## 💻 Stack Tecnológico

### Backend

| Tecnología | Versión | Uso |
|------------|---------|-----|
| **PHP** | 8.2+ | Lenguaje base |
| **Laravel** | 10.x | Framework principal |
| **MySQL** | 8.0+ | Base principal |
| **SQLite** | 3.35+ | Bases de tenants |
| **Composer** | 2.x | Gestión de dependencias |

### Frontend

| Tecnología | Versión | Uso |
|------------|---------|-----|
| **Bootstrap** | 5.3 | Framework CSS |
| **Bootstrap Icons** | 1.11 | Iconografía |
| **JavaScript Vanilla** | ES6+ | Interactividad |
| **Blade** | Laravel 10 | Motor de templates |

### Herramientas

| Herramienta | Uso |
|-------------|-----|
| **Git** | Control de versiones |
| **Artisan** | CLI de Laravel |
| **Composer** | Dependencias PHP |

---

## 📁 Estructura de Directorios

```
puntos/
├── app/                          # Aplicación Laravel
│   ├── app/
│   │   ├── Console/
│   │   │   └── Commands/         # Comandos Artisan custom
│   │   │       ├── SetupTenantDatabase.php
│   │   │       └── QueryTenantData.php
│   │   ├── Http/
│   │   │   ├── Controllers/      # Controladores
│   │   │   │   ├── Api/
│   │   │   │   │   └── WebhookController.php
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ClienteController.php
│   │   │   │   ├── PuntosController.php
│   │   │   │   ├── PromocionController.php
│   │   │   │   ├── ReporteController.php
│   │   │   │   ├── UsuarioController.php
│   │   │   │   ├── ConfiguracionController.php
│   │   │   │   └── AutoconsultaController.php
│   │   │   └── Middleware/       # Middleware custom
│   │   │       ├── IdentifyTenant.php
│   │   │       ├── AuthenticateTenant.php
│   │   │       └── CheckRole.php
│   │   ├── Models/               # Modelos Eloquent
│   │   │   ├── Tenant.php
│   │   │   ├── SystemConfig.php
│   │   │   ├── Cliente.php
│   │   │   ├── Usuario.php
│   │   │   ├── Factura.php
│   │   │   ├── PuntosCanjeado.php
│   │   │   ├── PuntosVencido.php
│   │   │   ├── Promocion.php
│   │   │   ├── Configuracion.php
│   │   │   └── Actividad.php
│   │   ├── Services/             # Servicios de negocio
│   │   │   └── PuntosService.php
│   │   ├── Adapters/             # Adapters de integración
│   │   │   └── EfacturaAdapter.php
│   │   ├── DTOs/                 # Data Transfer Objects
│   │   │   └── StandardInvoiceDTO.php
│   │   └── Contracts/            # Interfaces
│   │       └── InvoiceAdapter.php
│   ├── database/
│   │   ├── migrations/           # Migraciones
│   │   │   ├── *.php             # Migr. main DB
│   │   │   └── tenant/           # Migr. tenants
│   │   └── seeders/              # Seeders
│   ├── resources/
│   │   └── views/                # Vistas Blade
│   │       ├── layouts/
│   │       │   └── app.blade.php
│   │       ├── auth/
│   │       ├── dashboard/
│   │       ├── clientes/
│   │       ├── puntos/
│   │       ├── promociones/
│   │       ├── reportes/
│   │       ├── usuarios/
│   │       ├── configuracion/
│   │       └── autoconsulta/
│   ├── routes/
│   │   ├── web.php               # Rutas web
│   │   └── api.php               # Rutas API
│   └── storage/
│       └── tenants/              # SQLite files
│           └── {rut}.sqlite
├── scripts/                      # Scripts auxiliares
│   ├── emulador_webhook.php
│   └── README.md
└── *.md                          # Documentación
```

---

## 🗄️ Base de Datos

### Base Principal (MySQL)

#### Tabla: `tenants`

Almacena información de los comercios (clientes del sistema).

```sql
CREATE TABLE tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rut VARCHAR(20) UNIQUE NOT NULL,
    nombre_comercial VARCHAR(255) NOT NULL,
    api_key VARCHAR(100) UNIQUE NOT NULL,
    estado ENUM('activo', 'suspendido', 'inactivo') DEFAULT 'activo',
    sqlite_path VARCHAR(500) NOT NULL,
    telefono VARCHAR(50),
    email VARCHAR(255),
    direccion TEXT,
    formato_factura VARCHAR(50) DEFAULT 'efactura',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_rut (rut),
    INDEX idx_api_key (api_key),
    INDEX idx_estado (estado)
);
```

**Campos clave:**
- `rut`: Identificador único del tenant
- `api_key`: Token para autenticación del webhook
- `sqlite_path`: Ruta al archivo SQLite del tenant
- `formato_factura`: Tipo de adapter a usar

#### Tabla: `system_config`

Configuración global del sistema.

```sql
CREATE TABLE system_config (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key VARCHAR(255) UNIQUE NOT NULL,
    value JSON,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_key (key)
);
```

**Configuraciones almacenadas:**
- `whatsapp`: Token, URL, configuración WhatsApp
- `email`: SMTP, credenciales de email
- `retencion_datos`: Política de limpieza

#### Tabla: `webhook_inbox_global`

Log global de todos los webhooks recibidos.

```sql
CREATE TABLE webhook_inbox_global (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_rut VARCHAR(20) NOT NULL,
    estado ENUM('exitoso', 'fallido') NOT NULL,
    origen VARCHAR(100) DEFAULT 'efactura',
    http_status INT,
    mensaje_error TEXT,
    payload_json JSON,
    procesado_en TIMESTAMP NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (tenant_rut) REFERENCES tenants(rut) ON DELETE CASCADE,
    INDEX idx_tenant_rut (tenant_rut),
    INDEX idx_estado (estado),
    INDEX idx_created_at (created_at)
);
```

### Bases de Tenants (SQLite)

Cada tenant tiene su propia base SQLite con las siguientes tablas:

#### Tabla: `clientes`

```sql
CREATE TABLE clientes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    documento VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    telefono VARCHAR(50),
    puntos_disponibles INTEGER DEFAULT 0,
    total_puntos_generados INTEGER DEFAULT 0,
    total_puntos_canjeados INTEGER DEFAULT 0,
    total_puntos_vencidos INTEGER DEFAULT 0,
    activo INTEGER DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE INDEX idx_clientes_documento ON clientes(documento);
CREATE INDEX idx_clientes_activo ON clientes(activo);
CREATE INDEX idx_clientes_puntos ON clientes(puntos_disponibles);
```

#### Tabla: `facturas`

```sql
CREATE TABLE facturas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id INTEGER NOT NULL,
    numero_factura VARCHAR(100) NOT NULL,
    fecha_emision DATE NOT NULL,
    monto_total DECIMAL(15,2) NOT NULL,
    puntos_generados INTEGER NOT NULL,
    puntos_disponibles INTEGER DEFAULT 0,
    puntos_canjeados INTEGER DEFAULT 0,
    fecha_vencimiento DATE,
    promocion_id INTEGER,
    promocion_nombre VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (promocion_id) REFERENCES promociones(id) ON DELETE SET NULL
);

CREATE INDEX idx_facturas_cliente ON facturas(cliente_id);
CREATE INDEX idx_facturas_numero ON facturas(numero_factura);
CREATE INDEX idx_facturas_fecha_emision ON facturas(fecha_emision);
CREATE INDEX idx_facturas_vencimiento ON facturas(fecha_vencimiento);
CREATE INDEX idx_facturas_puntos_disponibles ON facturas(puntos_disponibles);
```

#### Tabla: `puntos_canjeados`

```sql
CREATE TABLE puntos_canjeados (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id INTEGER NOT NULL,
    usuario_id INTEGER NOT NULL,
    puntos_canjeados INTEGER NOT NULL,
    codigo_cupon VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE INDEX idx_canjes_cliente ON puntos_canjeados(cliente_id);
CREATE INDEX idx_canjes_codigo ON puntos_canjeados(codigo_cupon);
CREATE INDEX idx_canjes_fecha ON puntos_canjeados(created_at);
```

#### Tabla: `puntos_vencidos`

```sql
CREATE TABLE puntos_vencidos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id INTEGER NOT NULL,
    factura_id INTEGER,
    puntos_vencidos INTEGER NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE SET NULL
);

CREATE INDEX idx_vencidos_cliente ON puntos_vencidos(cliente_id);
CREATE INDEX idx_vencidos_fecha ON puntos_vencidos(fecha_vencimiento);
```

#### Tabla: `promociones`

```sql
CREATE TABLE promociones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    tipo ENUM('descuento', 'bonificacion', 'multiplicador') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    condiciones TEXT,
    fecha_inicio DATE,
    fecha_fin DATE,
    prioridad INTEGER DEFAULT 50,
    activa INTEGER DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE INDEX idx_promociones_activa ON promociones(activa);
CREATE INDEX idx_promociones_fechas ON promociones(fecha_inicio, fecha_fin);
CREATE INDEX idx_promociones_tipo ON promociones(tipo);
CREATE INDEX idx_promociones_prioridad ON promociones(prioridad);
```

#### Tabla: `configuracion`

```sql
CREATE TABLE configuracion (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE INDEX idx_configuracion_key ON configuracion(key);
```

**Configuraciones almacenadas:**
- `puntos_por_pesos`: Tasa de conversión
- `dias_vencimiento`: Días hasta vencer puntos
- `contacto`: Datos del comercio
- `eventos_whatsapp`: Qué eventos notificar

#### Tabla: `usuarios`

```sql
CREATE TABLE usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'supervisor', 'operario') NOT NULL,
    activo INTEGER DEFAULT 1,
    ultimo_acceso TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_rol ON usuarios(rol);
CREATE INDEX idx_usuarios_activo ON usuarios(activo);
```

#### Tabla: `actividades`

```sql
CREATE TABLE actividades (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT,
    datos_json TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP
);

CREATE INDEX idx_actividades_usuario ON actividades(usuario_id);
CREATE INDEX idx_actividades_accion ON actividades(accion);
CREATE INDEX idx_actividades_fecha ON actividades(created_at);
```

#### Tabla: `webhook_inbox`

```sql
CREATE TABLE webhook_inbox (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    numero_factura VARCHAR(100),
    estado ENUM('procesado', 'error') NOT NULL,
    payload_json TEXT,
    mensaje TEXT,
    created_at TIMESTAMP
);

CREATE INDEX idx_webhook_inbox_factura ON webhook_inbox(numero_factura);
CREATE INDEX idx_webhook_inbox_estado ON webhook_inbox(estado);
```

#### Tabla: `whatsapp_logs`

```sql
CREATE TABLE whatsapp_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id INTEGER NOT NULL,
    telefono VARCHAR(50) NOT NULL,
    mensaje TEXT NOT NULL,
    evento VARCHAR(100),
    estado ENUM('enviado', 'fallido', 'pendiente') NOT NULL,
    respuesta TEXT,
    intentos INTEGER DEFAULT 1,
    created_at TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

CREATE INDEX idx_whatsapp_cliente ON whatsapp_logs(cliente_id);
CREATE INDEX idx_whatsapp_estado ON whatsapp_logs(estado);
```

---

## 🎯 Modelos Eloquent

### Modelo Base de Tenant

Todos los modelos de tenant deben usar la conexión correcta:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantModel extends Model
{
    protected $connection = 'tenant'; // Usa conexión del tenant actual
}
```

### Ejemplo: Cliente

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $connection = 'tenant';
    protected $table = 'clientes';
    
    protected $fillable = [
        'documento',
        'nombre',
        'email',
        'telefono',
        'puntos_disponibles',
        'total_puntos_generados',
        'total_puntos_canjeados',
        'total_puntos_vencidos',
        'activo',
    ];
    
    protected $casts = [
        'puntos_disponibles' => 'integer',
        'total_puntos_generados' => 'integer',
        'total_puntos_canjeados' => 'integer',
        'total_puntos_vencidos' => 'integer',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relationships
    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }
    
    public function puntosCanjeados()
    {
        return $this->hasMany(PuntosCanjeado::class);
    }
    
    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }
    
    public function scopeConPuntos($query)
    {
        return $query->where('puntos_disponibles', '>', 0);
    }
    
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('documento', 'LIKE', "%{$termino}%")
              ->orWhere('nombre', 'LIKE', "%{$termino}%");
        });
    }
    
    // Accessors
    public function getPuntosFormatadosAttribute()
    {
        return number_format($this->puntos_disponibles, 2, ',', '.');
    }
    
    public function getTelefonoWhatsappAttribute()
    {
        if (empty($this->telefono)) {
            return null;
        }
        
        // Formatear para WhatsApp: +598XXXXXXXXX
        $telefono = preg_replace('/[^0-9]/', '', $this->telefono);
        
        if (strlen($telefono) === 9 && substr($telefono, 0, 1) === '0') {
            return '+598' . $telefono;
        }
        
        return '+598' . $telefono;
    }
    
    public function getBadgeEstadoAttribute()
    {
        return $this->activo 
            ? '<span class="badge bg-success">Activo</span>' 
            : '<span class="badge bg-secondary">Inactivo</span>';
    }
    
    public function getInicialesAttribute()
    {
        $palabras = explode(' ', $this->nombre);
        $iniciales = '';
        
        foreach (array_slice($palabras, 0, 2) as $palabra) {
            $iniciales .= strtoupper(substr($palabra, 0, 1));
        }
        
        return $iniciales;
    }
}
```

### Relationships Comunes

#### One-to-Many

```php
// Un cliente tiene muchas facturas
public function facturas()
{
    return $this->hasMany(Factura::class);
}

// Una factura pertenece a un cliente
public function cliente()
{
    return $this->belongsTo(Cliente::class);
}
```

#### With (Eager Loading)

```php
// Cargar facturas al consultar clientes
$clientes = Cliente::with('facturas')->get();

// Cargar solo campos específicos
$clientes = Cliente::with('facturas:id,cliente_id,puntos_disponibles')->get();
```

### Scopes Reutilizables

```php
// Scope local
public function scopeActivos($query)
{
    return $query->where('activo', 1);
}

// Uso
$clientesActivos = Cliente::activos()->get();

// Scope con parámetros
public function scopeConPuntosMinimo($query, $minimo)
{
    return $query->where('puntos_disponibles', '>=', $minimo);
}

// Uso
$clientesVIP = Cliente::conPuntosMinimo(1000)->get();
```

### Accessors (Atributos Calculados)

```php
// Accessor
public function getPuntosFormatadosAttribute()
{
    return number_format($this->puntos_disponibles, 2, ',', '.');
}

// Uso
$cliente->puntos_formateados; // "1.234,56"
```

### Mutators (Modificar antes de guardar)

```php
// Mutator
public function setNombreAttribute($value)
{
    $this->attributes['nombre'] = ucwords(strtolower($value));
}

// Uso
$cliente->nombre = "JUAN PÉREZ"; // Se guarda como "Juan Pérez"
```

---

## 🛡️ Middleware

### 1. IdentifyTenant

**Archivo:** `app/Http/Middleware/IdentifyTenant.php`

**Propósito:** Identificar el tenant según la URL y configurar la conexión SQLite.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        $tenantRut = $request->route('tenant');
        
        if (!$tenantRut) {
            abort(404, 'Tenant no especificado');
        }
        
        // Buscar tenant en MySQL
        $tenant = Tenant::where('rut', $tenantRut)
            ->where('estado', 'activo')
            ->first();
        
        if (!$tenant) {
            abort(404, 'Tenant no encontrado o inactivo');
        }
        
        // Configurar conexión SQLite del tenant
        $sqlitePath = $tenant->getSqlitePath();
        
        if (!file_exists($sqlitePath)) {
            abort(500, 'Base de datos del tenant no encontrada');
        }
        
        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => $sqlitePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        
        // Purgar y reconectar
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        // Guardar tenant en request
        $request->attributes->add(['tenant' => $tenant]);
        
        // Compartir con vistas
        view()->share('tenant', $tenant);
        
        return $next($request);
    }
}
```

**Flujo:**
1. Extrae `{tenant}` de la URL
2. Busca el tenant en MySQL
3. Configura la conexión SQLite
4. Comparte el tenant con la request y vistas

### 2. AuthenticateTenant

**Archivo:** `app/Http/Middleware/AuthenticateTenant.php`

**Propósito:** Verificar que el usuario esté autenticado en el tenant.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Usuario;

class AuthenticateTenant
{
    public function handle(Request $request, Closure $next)
    {
        $userId = session('usuario_id');
        
        if (!$userId) {
            $tenantRut = $request->route('tenant');
            return redirect("/{$tenantRut}/login")
                ->with('error', 'Debe iniciar sesión para continuar');
        }
        
        // Obtener usuario del tenant actual (SQLite)
        $usuario = Usuario::where('id', $userId)
            ->where('activo', 1)
            ->first();
        
        if (!$usuario) {
            session()->forget(['usuario_id', 'usuario_nombre', 'usuario_rol']);
            
            $tenantRut = $request->route('tenant');
            return redirect("/{$tenantRut}/login")
                ->with('error', 'Usuario no autorizado o inactivo');
        }
        
        // Actualizar último acceso
        $usuario->ultimo_acceso = now();
        $usuario->save();
        
        // Guardar en request y vistas
        $request->attributes->add(['usuario' => $usuario]);
        view()->share('usuario', $usuario);
        
        return $next($request);
    }
}
```

### 3. CheckRole

**Archivo:** `app/Http/Middleware/CheckRole.php`

**Propósito:** Verificar que el usuario tenga el rol necesario.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $usuario = $request->attributes->get('usuario');
        
        if (!$usuario) {
            abort(403, 'No autenticado');
        }
        
        if (!in_array($usuario->rol, $roles)) {
            abort(403, 'No tiene permisos para acceder a esta sección');
        }
        
        return $next($request);
    }
}
```

**Uso en rutas:**

```php
Route::middleware(['tenant', 'auth.tenant', 'role:admin,supervisor'])
    ->group(function () {
        Route::get('/puntos/canjear', [PuntosController::class, 'mostrarFormulario']);
    });
```

---

## 🎮 Controladores

### Estructura de un Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Actividad;

class ClienteController extends Controller
{
    /**
     * Listar clientes con filtros y búsqueda
     * 
     * GET /{tenant}/clientes
     */
    public function index(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        $query = Cliente::query();
        
        // Búsqueda
        if ($request->has('buscar') && !empty($request->buscar)) {
            $query->buscar($request->buscar);
        }
        
        // Filtros
        if ($request->filtro === 'activos') {
            $query->activos();
        } elseif ($request->filtro === 'con_puntos') {
            $query->conPuntos();
        }
        
        // Orden
        $query->orderBy('created_at', 'desc');
        
        // Paginación
        $clientes = $query->paginate(15);
        
        return view('clientes.index', compact('tenant', 'usuario', 'clientes'));
    }
    
    /**
     * Ver detalle de un cliente
     * 
     * GET /{tenant}/clientes/{id}
     */
    public function show(Request $request, $id)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');
        
        $cliente = Cliente::with([
            'facturas' => function ($query) {
                $query->where('puntos_disponibles', '>', 0)
                    ->orderBy('fecha_emision', 'asc');
            },
            'puntosCanjeados' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }
        ])->findOrFail($id);
        
        return view('clientes.show', compact('tenant', 'usuario', 'cliente'));
    }
    
    /**
     * Actualizar cliente
     * 
     * PUT /{tenant}/clientes/{id}
     */
    public function update(Request $request, $id)
    {
        $usuario = $request->attributes->get('usuario');
        $tenant = $request->attributes->get('tenant');
        
        // Validación
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:50',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'email.email' => 'El email debe ser válido',
        ]);
        
        $cliente = Cliente::findOrFail($id);
        $cliente->update($validated);
        
        // Registrar actividad
        Actividad::registrar(
            $usuario->id,
            Actividad::ACCION_ACTUALIZAR_CLIENTE,
            "Cliente actualizado: {$cliente->nombre}",
            ['cliente_id' => $cliente->id, 'cambios' => $validated]
        );
        
        return redirect("/{$tenant->rut}/clientes/{$id}")
            ->with('success', 'Cliente actualizado correctamente');
    }
}
```

### Patrón de Validación

```php
$validated = $request->validate([
    'campo' => 'required|string|max:255',
    'email' => 'required|email|unique:usuarios,email',
    'numero' => 'required|integer|min:1',
], [
    // Mensajes personalizados
    'campo.required' => 'El campo es obligatorio',
    'email.email' => 'Debe ser un email válido',
]);
```

### Registro de Actividades

Siempre registrar acciones importantes:

```php
Actividad::registrar(
    $usuario->id,
    Actividad::ACCION_CANJE_PUNTOS,
    "Canje de {$puntos} puntos para {$cliente->nombre}",
    [
        'cliente_id' => $cliente->id,
        'puntos' => $puntos,
        'codigo_cupon' => $cupon->codigo,
    ]
);
```

---

## 🛣️ Rutas

### Estructura de routes/web.php

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
// ... otros imports

/*
|--------------------------------------------------------------------------
| Rutas del Tenant
|--------------------------------------------------------------------------
| Todas las rutas multi-tenant siguen el patrón: /{tenant}/...
*/

Route::prefix('{tenant}')->group(function () {
    
    // ==================== AUTENTICACIÓN ====================
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('tenant.login.form');
    Route::post('/login', [AuthController::class, 'login'])->name('tenant.login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('tenant.logout');
    
    // ==================== RUTAS PROTEGIDAS ====================
    Route::middleware(['tenant', 'auth.tenant'])->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
        
        // Clientes (todos los roles)
        Route::prefix('clientes')->group(function () {
            Route::get('/', [ClienteController::class, 'index'])->name('tenant.clientes');
            Route::get('/buscar', [ClienteController::class, 'buscar'])->name('tenant.clientes.buscar');
            Route::get('/{id}', [ClienteController::class, 'show'])->name('tenant.clientes.show');
            
            // Solo Admin y Supervisor
            Route::middleware(['role:admin,supervisor'])->group(function () {
                Route::get('/{id}/editar', [ClienteController::class, 'edit'])->name('tenant.clientes.edit');
                Route::put('/{id}', [ClienteController::class, 'update'])->name('tenant.clientes.update');
            });
        });
        
        // Canje de Puntos (Admin y Supervisor)
        Route::middleware(['role:admin,supervisor'])->prefix('puntos')->group(function () {
            Route::get('/canjear', [PuntosController::class, 'mostrarFormulario'])->name('tenant.puntos.canjear');
            Route::post('/buscar', [PuntosController::class, 'buscarCliente'])->name('tenant.puntos.buscar');
            Route::post('/procesar', [PuntosController::class, 'procesar'])->name('tenant.puntos.procesar');
            Route::get('/cupon/{id}', [PuntosController::class, 'mostrarCupon'])->name('tenant.puntos.cupon');
        });
        
        // Reportes (todos los roles)
        Route::prefix('reportes')->group(function () {
            Route::get('/', [ReporteController::class, 'index'])->name('tenant.reportes');
            Route::get('/clientes', [ReporteController::class, 'clientes'])->name('tenant.reportes.clientes');
            Route::get('/facturas', [ReporteController::class, 'facturas'])->name('tenant.reportes.facturas');
            Route::get('/canjes', [ReporteController::class, 'canjes'])->name('tenant.reportes.canjes');
            Route::get('/actividades', [ReporteController::class, 'actividades'])->name('tenant.reportes.actividades');
        });
        
        // ==================== SOLO ADMIN ====================
        Route::middleware(['role:admin'])->group(function () {
            
            // Promociones
            Route::resource('promociones', PromocionController::class)->except(['show']);
            Route::post('/promociones/{id}/toggle', [PromocionController::class, 'toggle'])->name('tenant.promociones.toggle');
            
            // Usuarios
            Route::prefix('usuarios')->group(function () {
                Route::get('/', [UsuarioController::class, 'index'])->name('tenant.usuarios');
                Route::get('/crear', [UsuarioController::class, 'create'])->name('tenant.usuarios.create');
                Route::post('/', [UsuarioController::class, 'store'])->name('tenant.usuarios.store');
                Route::get('/{id}/editar', [UsuarioController::class, 'edit'])->name('tenant.usuarios.edit');
                Route::put('/{id}', [UsuarioController::class, 'update'])->name('tenant.usuarios.update');
                Route::post('/{id}/cambiar-password', [UsuarioController::class, 'cambiarPassword'])->name('tenant.usuarios.password');
                Route::post('/{id}/toggle', [UsuarioController::class, 'toggle'])->name('tenant.usuarios.toggle');
            });
            
            // Configuración
            Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('tenant.configuracion');
            Route::post('/configuracion/puntos', [ConfiguracionController::class, 'actualizarPuntos'])->name('tenant.configuracion.puntos');
            Route::post('/configuracion/vencimiento', [ConfiguracionController::class, 'actualizarVencimiento'])->name('tenant.configuracion.vencimiento');
            Route::post('/configuracion/contacto', [ConfiguracionController::class, 'actualizarContacto'])->name('tenant.configuracion.contacto');
            Route::post('/configuracion/whatsapp', [ConfiguracionController::class, 'actualizarWhatsApp'])->name('tenant.configuracion.whatsapp');
        });
    });
    
    // ==================== PORTAL PÚBLICO ====================
    Route::get('/consulta', [AutoconsultaController::class, 'index'])->name('tenant.consulta');
    Route::post('/consulta', [AutoconsultaController::class, 'consultar'])->name('tenant.consulta.procesar');
    Route::post('/consulta/actualizar-contacto', [AutoconsultaController::class, 'actualizarContacto'])->name('tenant.consulta.actualizar');
});
```

### Nomenclatura de Rutas

| Acción | Método HTTP | Ruta | Nombre |
|--------|-------------|------|--------|
| Listar | GET | `/clientes` | `tenant.clientes` |
| Ver | GET | `/clientes/{id}` | `tenant.clientes.show` |
| Crear (form) | GET | `/clientes/crear` | `tenant.clientes.create` |
| Guardar | POST | `/clientes` | `tenant.clientes.store` |
| Editar (form) | GET | `/clientes/{id}/editar` | `tenant.clientes.edit` |
| Actualizar | PUT/PATCH | `/clientes/{id}` | `tenant.clientes.update` |
| Eliminar | DELETE | `/clientes/{id}` | `tenant.clientes.destroy` |

---

## 📡 Webhook y Adapters

### Flujo del Webhook

```
1. eFactura → POST /api/webhook/ingest
2. WebhookController::ingest()
3. Validar API Key y RUT
4. EfacturaAdapter::toStandard()
5. PuntosService::procesarFactura()
6. Registrar en webhook_inbox
7. Retornar respuesta JSON
```

### WebhookController

**Archivo:** `app/Http/Controllers/Api/WebhookController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Adapters\EfacturaAdapter;
use App\Services\PuntosService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class WebhookController extends Controller
{
    public function ingest(Request $request)
    {
        $apiKey = $request->bearerToken();
        $payload = $request->all();
        
        // 1. Validar API Key
        $tenant = Tenant::where('api_key', $apiKey)
            ->where('estado', 'activo')
            ->first();
        
        if (!$tenant) {
            return $this->errorResponse('API Key inválida o tenant inactivo', 401);
        }
        
        // 2. Configurar conexión SQLite del tenant
        $this->conectarTenant($tenant);
        
        // 3. Obtener adapter según formato
        $adapter = $this->obtenerAdaptador($tenant->formato_factura);
        
        // 4. Validar que el payload coincida con el adapter
        if (!$adapter->matches($payload)) {
            $this->registrarWebhookGlobal($tenant->rut, 'fallido', 400, 'Formato de factura no reconocido', $payload);
            return $this->errorResponse('Formato de factura no reconocido', 400);
        }
        
        try {
            // 5. Convertir a formato estándar
            $facturaDTO = $adapter->toStandard($payload);
            
            // 6. Procesar factura y generar puntos
            $puntosService = new PuntosService();
            $resultado = $puntosService->procesarFactura($facturaDTO);
            
            // 7. Registrar webhook exitoso
            $this->registrarWebhookGlobal($tenant->rut, 'exitoso', 200, 'Factura procesada correctamente', $payload, now());
            $this->registrarWebhookTenant($facturaDTO->numeroFactura, 'procesado', $payload, 'Factura procesada');
            
            // 8. Retornar respuesta
            return response()->json([
                'success' => true,
                'message' => 'Factura procesada correctamente',
                'data' => [
                    'numero_factura' => $resultado['factura']->numero_factura,
                    'cliente' => $resultado['cliente']->nombre,
                    'puntos_generados' => $resultado['factura']->puntos_generados,
                    'promocion_aplicada' => $resultado['promocion'] ?? null,
                ],
            ], 200);
            
        } catch (\Exception $e) {
            $this->registrarWebhookGlobal($tenant->rut, 'fallido', 500, $e->getMessage(), $payload);
            $this->registrarWebhookTenant($payload['Numero'] ?? 'unknown', 'error', $payload, $e->getMessage());
            
            return $this->errorResponse('Error al procesar factura: ' . $e->getMessage(), 500);
        }
    }
    
    private function conectarTenant(Tenant $tenant)
    {
        $sqlitePath = $tenant->getSqlitePath();
        
        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => $sqlitePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        
        DB::purge('tenant');
        DB::reconnect('tenant');
    }
    
    private function obtenerAdaptador($formato)
    {
        return match ($formato) {
            'efactura' => new EfacturaAdapter(),
            default => new EfacturaAdapter(),
        };
    }
    
    private function errorResponse($message, $code)
    {
        return response()->json([
            'success' => false,
            'error' => $message,
        ], $code);
    }
}
```

### EfacturaAdapter

**Archivo:** `app/Adapters/EfacturaAdapter.php`

```php
<?php

namespace App\Adapters;

use App\Contracts\InvoiceAdapter;
use App\DTOs\StandardInvoiceDTO;

class EfacturaAdapter implements InvoiceAdapter
{
    public function matches(array $payload): bool
    {
        return isset($payload['Numero']) 
            && isset($payload['Client']) 
            && isset($payload['Total']);
    }
    
    public function toStandard(array $payload): StandardInvoiceDTO
    {
        $dto = new StandardInvoiceDTO();
        
        $dto->numeroFactura = (string) $payload['Numero'];
        $dto->fechaEmision = $payload['Fecha'] ?? date('Y-m-d');
        $dto->montoTotal = (float) ($payload['Total']['TotMntTotal'] ?? 0);
        
        $dto->documentoCliente = (string) ($payload['Client']['NroDoc'] ?? '');
        $dto->nombreCliente = (string) ($payload['Client']['RznSocial'] ?? 'Cliente Sin Nombre');
        $dto->telefonoCliente = $payload['Client']['NroTel'] ?? null;
        $dto->emailCliente = $payload['Client']['Email'] ?? null;
        
        // Extraer RUT del emisor
        if (isset($payload['Emisor']['RUCEmisor'])) {
            $dto->rutEmisor = (string) $payload['Emisor']['RUCEmisor'];
        } elseif (isset($payload['Emisor']['RUT'])) {
            $dto->rutEmisor = (string) $payload['Emisor']['RUT'];
        } else {
            $dto->rutEmisor = null;
        }
        
        return $dto;
    }
}
```

### StandardInvoiceDTO

**Archivo:** `app/DTOs/StandardInvoiceDTO.php`

```php
<?php

namespace App\DTOs;

class StandardInvoiceDTO
{
    public string $numeroFactura;
    public string $fechaEmision;
    public float $montoTotal;
    public string $documentoCliente;
    public string $nombreCliente;
    public ?string $telefonoCliente;
    public ?string $emailCliente;
    public ?string $rutEmisor;
}
```

---

## ⚙️ Servicios

### PuntosService

**Archivo:** `app/Services/PuntosService.php`

**Responsabilidades:**
- Calcular puntos según configuración
- Aplicar promociones
- Crear/actualizar cliente
- Guardar factura
- Registrar actividad

```php
<?php

namespace App\Services;

use App\DTOs\StandardInvoiceDTO;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Configuracion;
use App\Models\Promocion;
use App\Models\Actividad;
use Illuminate\Support\Facades\DB;

class PuntosService
{
    public function procesarFactura(StandardInvoiceDTO $facturaDTO): array
    {
        return DB::transaction(function () use ($facturaDTO) {
            
            // 1. Obtener configuración
            $puntosporPesos = Configuracion::get('puntos_por_pesos', 100);
            $diasVencimiento = Configuracion::get('dias_vencimiento', 180);
            
            // 2. Buscar o crear cliente
            $cliente = Cliente::firstOrCreate(
                ['documento' => $facturaDTO->documentoCliente],
                [
                    'nombre' => $facturaDTO->nombreCliente,
                    'email' => $facturaDTO->emailCliente,
                    'telefono' => $facturaDTO->telefonoCliente,
                    'activo' => 1,
                ]
            );
            
            // 3. Calcular puntos base
            $puntosBase = floor($facturaDTO->montoTotal / $puntosporPesos);
            
            // 4. Aplicar promoción si existe
            $promocionAplicada = Promocion::aplicar($facturaDTO->montoTotal, $facturaDTO->fechaEmision);
            
            $puntosFinales = $puntosBase;
            if ($promocionAplicada) {
                $puntosFinales = $this->aplicarPromocion($puntosBase, $promocionAplicada);
            }
            
            // 5. Crear factura
            $factura = Factura::create([
                'cliente_id' => $cliente->id,
                'numero_factura' => $facturaDTO->numeroFactura,
                'fecha_emision' => $facturaDTO->fechaEmision,
                'monto_total' => $facturaDTO->montoTotal,
                'puntos_generados' => $puntosFinales,
                'puntos_disponibles' => $puntosFinales,
                'fecha_vencimiento' => date('Y-m-d', strtotime($facturaDTO->fechaEmision . " + {$diasVencimiento} days")),
                'promocion_id' => $promocionAplicada->id ?? null,
                'promocion_nombre' => $promocionAplicada->nombre ?? null,
            ]);
            
            // 6. Actualizar cliente
            $cliente->increment('puntos_disponibles', $puntosFinales);
            $cliente->increment('total_puntos_generados', $puntosFinales);
            
            // 7. Registrar actividad
            Actividad::registrar(
                null,
                Actividad::ACCION_FACTURA_PROCESADA,
                "Factura {$factura->numero_factura} procesada: {$puntosFinales} puntos generados",
                [
                    'factura_id' => $factura->id,
                    'cliente_id' => $cliente->id,
                    'puntos' => $puntosFinales,
                    'promocion' => $promocionAplicada->nombre ?? null,
                ]
            );
            
            return [
                'cliente' => $cliente,
                'factura' => $factura,
                'promocion' => $promocionAplicada,
            ];
        });
    }
    
    private function aplicarPromocion(int $puntosBase, Promocion $promocion): int
    {
        return match ($promocion->tipo) {
            'multiplicador' => $puntosBase * $promocion->valor,
            'bonificacion' => $puntosBase + $promocion->valor,
            'descuento' => $puntosBase, // No afecta puntos generados
            default => $puntosBase,
        };
    }
}
```

---

## 🔧 Comandos Artisan

### SetupTenantDatabase

**Archivo:** `app/Console/Commands/SetupTenantDatabase.php`

**Uso:**
```bash
php artisan tenant:setup-database {rut}
```

**Qué hace:**
1. Busca el tenant en MySQL
2. Crea el archivo SQLite si no existe
3. Ejecuta las migraciones del tenant
4. Verifica que las tablas se crearon

### QueryTenantData

**Archivo:** `app/Console/Commands/QueryTenantData.php`

**Uso:**
```bash
php artisan tenant:query {rut}
```

**Qué hace:**
1. Conecta a la base SQLite del tenant
2. Muestra estadísticas:
   - Total de clientes
   - Puntos totales
   - Facturas del mes
   - Últimos 5 clientes

---

## 🎨 Patrones de Diseño

### 1. Adapter Pattern

**Problema:** Diferentes formatos de facturas electrónicas.

**Solución:** Interface común `InvoiceAdapter` con múltiples implementaciones.

```php
// Interface
interface InvoiceAdapter {
    public function matches(array $payload): bool;
    public function toStandard(array $payload): StandardInvoiceDTO;
}

// Implementaciones
class EfacturaAdapter implements InvoiceAdapter { ... }
class OtroFormatoAdapter implements InvoiceAdapter { ... }
```

### 2. Repository Pattern

**Problema:** Lógica de base de datos mezclada con controladores.

**Solución:** Métodos estáticos en modelos y servicios dedicados.

```php
// En lugar de:
$clientes = DB::table('clientes')->where('activo', 1)->get();

// Usar:
$clientes = Cliente::activos()->get();
```

### 3. Service Layer

**Problema:** Lógica de negocio compleja en controladores.

**Solución:** Servicios dedicados como `PuntosService`.

```php
// En lugar de todo en el controlador:
public function procesar(Request $request) {
    // 100 líneas de lógica...
}

// Delegar a servicio:
public function procesar(Request $request) {
    $service = new PuntosService();
    return $service->procesarFactura($dto);
}
```

### 4. DTO (Data Transfer Object)

**Problema:** Pasar muchos parámetros entre capas.

**Solución:** Objetos de transferencia de datos.

```php
// En lugar de:
function procesar($numero, $fecha, $monto, $cliente, $telefono, ...) { }

// Usar DTO:
function procesar(StandardInvoiceDTO $dto) { }
```

---

## 🔒 Seguridad

### Autenticación

#### Hashing de Contraseñas

```php
// Guardar
$usuario->password = bcrypt($request->password);

// Verificar
if (Hash::check($request->password, $usuario->password)) {
    // Correcto
}
```

#### Sesiones

```php
// Guardar en sesión
session(['usuario_id' => $usuario->id]);

// Obtener
$userId = session('usuario_id');

// Eliminar
session()->forget('usuario_id');
```

### Autorización

#### Middleware de Roles

```php
Route::middleware(['role:admin'])->group(function () {
    // Solo admins
});
```

#### En Controladores

```php
if (!$usuario->isAdmin()) {
    abort(403, 'No autorizado');
}
```

### Validación de Datos

```php
$validated = $request->validate([
    'email' => 'required|email|unique:usuarios',
    'password' => 'required|min:6',
]);
```

### Protección CSRF

Todos los formularios incluyen:

```blade
<form method="POST">
    @csrf
    <!-- campos -->
</form>
```

### SQL Injection

Eloquent y Query Builder previenen automáticamente:

```php
// Seguro
Cliente::where('documento', $request->documento)->first();

// NO hacer
DB::select("SELECT * FROM clientes WHERE documento = '{$request->documento}'");
```

---

## 🧪 Testing

### Emulador de Webhook

**Archivo:** `scripts/emulador_webhook.php`

**Uso:**
```bash
cd scripts
php emulador_webhook.php [--flags]
```

**Flags disponibles:**
- `--sin-telefono`: Envía factura sin teléfono
- `--rut-incorrecto`: Usa RUT inexistente
- `--api-key-mala`: Usa API Key inválida

**Ejemplos:**
```bash
# Normal
php emulador_webhook.php

# Sin teléfono
php emulador_webhook.php --sin-telefono

# Forzar error de autenticación
php emulador_webhook.php --api-key-mala
```

### Testing Manual

1. **Crear tenant de prueba**
```bash
php artisan tenant:setup-database 000000000016
php artisan db:seed --class=TenantUserSeeder
```

2. **Probar webhook**
```bash
cd scripts
php emulador_webhook.php
```

3. **Verificar datos**
```bash
php artisan tenant:query 000000000016
```

4. **Login y navegar**
```
URL: http://localhost:8000/000000000016/login
User: admin@demo.com
Pass: 123456
```

---

## 📚 API Reference

### Webhook Endpoint

**Endpoint:** `POST /api/webhook/ingest`

**Headers:**
```
Authorization: Bearer {api-key}
Content-Type: application/json
```

**Request Body:**
```json
{
  "Numero": "A-12345",
  "Fecha": "2025-09-30",
  "Emisor": {
    "RUCEmisor": "000000000016"
  },
  "Client": {
    "NroDoc": "12345678",
    "RznSocial": "Juan Pérez",
    "NroTel": "099123456",
    "Email": "juan@email.com"
  },
  "Total": {
    "TotMntTotal": 1500.00
  }
}
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "Factura procesada correctamente",
  "data": {
    "numero_factura": "A-12345",
    "cliente": "Juan Pérez",
    "puntos_generados": 15,
    "promocion_aplicada": "Puntos Dobles"
  }
}
```

**Response Error (401):**
```json
{
  "success": false,
  "error": "API Key inválida o tenant inactivo"
}
```

**Response Error (400):**
```json
{
  "success": false,
  "error": "Formato de factura no reconocido"
}
```

**Response Error (500):**
```json
{
  "success": false,
  "error": "Error al procesar factura: {detalle}"
}
```

---

## 📝 Notas Finales

### Convenciones de Código

1. **PSR-12** para estilo de código PHP
2. **Camel Case** para métodos y variables
3. **Pascal Case** para clases
4. **Snake Case** para nombres de tablas y columnas
5. **Comentarios** en español
6. **Documentación** con PHPDoc

### Performance

- Usar **Eager Loading** para relaciones: `with()`
- Limitar resultados: `limit()`, `paginate()`
- Índices en columnas frecuentes
- Cache para configuraciones

### Mantenimiento

- **Backups diarios** de SQLite files
- **Logs** en `storage/logs/laravel.log`
- **Monitorear** `webhook_inbox_global`
- **Limpiar** datos antiguos según política

---

**Documentación técnica completa.**  
**Última actualización:** 30 de Septiembre de 2025  
**Versión del sistema:** 1.0
