# Esquema de Base de Datos - Sistema de Puntos
**Última actualización:** 11/02/2026

---

## Arquitectura

El sistema usa **dos tipos de base de datos**:

- **MySQL** (`puntos_main`): Base global compartida. Almacena tenants, usuarios superadmin, jobs de cola, y log global de webhooks.
- **SQLite** (una por tenant): Base individual por comercio. Almacena clientes, facturas, puntos, campañas, configuración, etc.

---

## 1. BASE DE DATOS MYSQL (Global - `puntos_main`)

### 1.1 `tenants`
Comercios/clientes del sistema.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigint PK | ID auto-incremental |
| `rut` | varchar(20) UNIQUE | RUT del comercio |
| `nombre_comercial` | varchar(255) | Nombre del negocio |
| `api_key` | varchar(100) UNIQUE | API Key para webhook (`tk_...`) |
| `estado` | enum(activo,suspendido,eliminado) | Estado del tenant |
| `sqlite_path` | varchar(500) | Ruta al archivo SQLite |
| `nombre_contacto` | varchar(255) | Contacto del comercio |
| `email_contacto` | varchar(255) | Email de contacto |
| `telefono_contacto` | varchar(50) | Teléfono de contacto |
| `direccion_contacto` | varchar(500) | Dirección |
| `formato_factura` | varchar(50) | Adaptador: efactura, factupronto, etc. |
| `ultimo_webhook` | timestamp | Fecha del último webhook recibido |
| `ultima_migracion` | timestamp | Fecha de última migración |
| `ultima_respaldo` | timestamp | Fecha de último backup |
| `facturas_recibidas` | bigint | Contador de facturas procesadas |
| `puntos_generados_total` | bigint | Contador de puntos generados |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |
| `deleted_at` | timestamp | Soft delete |

### 1.2 `users`
Usuarios superadmin del sistema central.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigint PK | |
| `name` | varchar(255) | Nombre |
| `email` | varchar(255) UNIQUE | Email |
| `email_verified_at` | timestamp | |
| `password` | varchar(255) | Hash de contraseña |
| `role` | varchar(50) | Rol (superadmin) |
| `status` | enum(active,inactive) | Estado |
| `remember_token` | varchar(100) | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### 1.3 `admin_logs`
Log de acciones del superadmin.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigint PK | |
| `user_id` | bigint FK → users | Usuario que realizó la acción |
| `accion` | varchar(150) | Tipo de acción |
| `descripcion` | text | Descripción detallada |
| `ip_address` | varchar(45) | IP del usuario |
| `metadata` | json | Datos adicionales |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### 1.4 `webhook_inbox_global`
Log global de todos los webhooks recibidos.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigint PK | |
| `tenant_rut` | varchar(20) FK → tenants.rut | RUT del tenant |
| `estado` | enum(pendiente,procesado,error,omitido) | Estado del procesamiento |
| `origen` | varchar(100) | Adaptador usado |
| `http_status` | int | Código HTTP de respuesta |
| `mensaje_error` | text | Error si falló |
| `payload_json` | text | JSON recibido (max 5000 chars) |
| `cfe_id` | int | Tipo de documento fiscal |
| `documento_cliente` | varchar(50) | Documento del cliente |
| `puntos_generados` | decimal(10,2) | Puntos generados |
| `motivo_no_acumulo` | varchar(255) | Razón si no acumuló |
| `procesado_en` | timestamp | Fecha de procesamiento |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### 1.5 `jobs`
Cola de trabajos asíncronos (Laravel Queue).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigint PK | |
| `queue` | varchar(255) | Nombre de la cola (ej: `campanas`) |
| `payload` | longtext | Datos serializados del Job |
| `attempts` | tinyint | Intentos realizados |
| `reserved_at` | int | Timestamp de reserva |
| `available_at` | int | Timestamp de disponibilidad (para delays) |
| `created_at` | int | Timestamp de creación |

### 1.6 `failed_jobs`
Jobs que fallaron después de todos los reintentos.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigint PK | |
| `uuid` | varchar(255) UNIQUE | Identificador único |
| `connection` | text | Conexión usada |
| `queue` | text | Cola |
| `payload` | longtext | Datos del Job |
| `exception` | longtext | Error/stack trace |
| `failed_at` | timestamp | Fecha de fallo |

### 1.7 `system_config`
Configuración global del sistema (WhatsApp, Email, retención).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigint PK | |
| `key` | varchar(100) UNIQUE | Clave (whatsapp, email, retencion_datos) |
| `value` | text | Valor en JSON (encriptado para whatsapp/email) |
| `description` | varchar(500) | Descripción |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### 1.8 `job_batches`
Lotes de jobs (Laravel Job Batching).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | varchar PK | UUID del lote |
| `name` | varchar | Nombre del lote |
| `total_jobs` | int | Total de jobs en el lote |
| `pending_jobs` | int | Jobs pendientes |
| `failed_jobs` | int | Jobs fallidos |
| `failed_job_ids` | longtext | IDs de jobs fallidos |
| `options` | mediumtext | Opciones serializadas |
| `cancelled_at` | int | Timestamp si fue cancelado |
| `created_at` | int | Timestamp de creación |
| `finished_at` | int | Timestamp de finalización |

### 1.9 Tablas auxiliares
- `migrations` - Control de migraciones Laravel
- `password_reset_tokens` - Tokens de reset de contraseña
- `personal_access_tokens` - Tokens de acceso API (Sanctum)

---

## 2. BASE DE DATOS SQLITE (Por Tenant)

Cada tenant tiene su propio archivo SQLite en `storage/tenants/{rut}.sqlite`.

### 2.1 `clientes`
Clientes finales del comercio.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | integer PK | |
| `documento` | varchar | Cédula/RUT del cliente |
| `nombre` | varchar | Nombre completo |
| `telefono` | varchar | Teléfono (formato 09XXXXXXX) |
| `email` | varchar | Email |
| `direccion` | varchar | Dirección |
| `puntos_acumulados` | numeric | **Saldo real de puntos** (fuente de verdad) |
| `ultima_actividad` | datetime | Última compra o canje |
| `created_at` | datetime | |
| `updated_at` | datetime | |

### 2.2 `facturas`
Facturas procesadas que generaron puntos. Se eliminan por FIFO al canjear.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | integer PK | |
| `cliente_id` | integer FK → clientes | Cliente asociado |
| `numero_factura` | varchar | Número de factura |
| `monto_total` | numeric | Monto total de la factura |
| `moneda` | varchar | Moneda (UYU, USD, etc.) |
| `puntos_generados` | numeric | Puntos que generó (se reduce con canjes, se pone 0 al vencer) |
| `promocion_aplicada` | varchar | ID de promoción si se aplicó |
| `cfe_id` | integer | Tipo de documento fiscal (101=factura, 102=NC, 111=e-factura, 112=e-NC) |
| `acumulo` | tinyint(1) | Si acumuló puntos (1) o no (0) |
| `motivo_no_acumulo` | varchar | Razón si no acumuló |
| `payload_json` | text | JSON original del webhook |
| `fecha_emision` | datetime | Fecha de emisión de la factura |
| `fecha_vencimiento` | datetime | Fecha en que vencen los puntos (emisión + días_config) |
| `created_at` | datetime | |
| `updated_at` | datetime | |

### 2.3 `puntos_canjeados`
Historial de canjes de puntos y ajustes manuales.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | integer PK | |
| `cliente_id` | integer FK → clientes | Cliente |
| `puntos_canjeados` | numeric | Cantidad de puntos (siempre positivo) |
| `puntos_restantes` | numeric | Saldo del cliente después del canje |
| `concepto` | varchar | Descripción del canje/ajuste |
| `autorizado_por` | varchar | ID del usuario que autorizó |
| `origen` | VARCHAR(50) | `panel`, `api`, `ajuste` |
| `referencia` | VARCHAR(255) | `ajuste_suma` o `ajuste_resta` para ajustes |
| `created_at` | datetime | |
| `updated_at` | datetime | |

### 2.4 `puntos_vencidos`
Historial de puntos que expiraron automáticamente.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | integer PK | |
| `cliente_id` | integer FK → clientes | Cliente |
| `puntos_vencidos` | numeric | Cantidad de puntos vencidos |
| `motivo` | varchar | "Vencimiento automático" |
| `created_at` | datetime | |
| `updated_at` | datetime | |

### 2.5 `configuracion`
Configuración del tenant (key-value en JSON).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | integer PK | |
| `key` | varchar | Clave (puntos_por_pesos, dias_vencimiento, eventos_whatsapp, etc.) |
| `value` | text | Valor en JSON |
| `created_at` | datetime | |
| `updated_at` | datetime | |

**Claves principales:**
- `puntos_por_pesos` → `{"valor": 100}` (cada 100 pesos = 1 punto)
- `dias_vencimiento` → `{"valor": 180}` (puntos vencen a los 180 días)
- `eventos_whatsapp` → `{"puntos_por_vencer": true, "canje": true, ...}`

### 2.6 `campanas`
Campañas de difusión masiva.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INTEGER PK | |
| `tenant_id` | INTEGER | ID del tenant |
| `canal` | VARCHAR(20) | `whatsapp` o `email` |
| `tipo_envio` | VARCHAR(20) | `todos`, `activos`, `inactivos` |
| `titulo` | VARCHAR(255) | Título de la campaña |
| `subtitulo` | VARCHAR(255) | Subtítulo |
| `imagen_url` | VARCHAR(500) | URL de imagen (email) |
| `asunto_email` | VARCHAR(255) | Asunto del email |
| `cuerpo_texto` | TEXT | Cuerpo HTML del email |
| `mensaje_whatsapp` | TEXT | Texto del mensaje WhatsApp |
| `fecha_programada` | DATETIME | Fecha/hora de envío (null = inmediato) |
| `estado` | VARCHAR(20) | `borrador`, `pendiente`, `en_cola`, `enviada`, `cancelada` |
| `totales` | TEXT | JSON con contadores `{clientes, whatsapp, email, exitosos, fallidos}` |
| `created_at` | DATETIME | |
| `updated_at` | DATETIME | |
| `deleted_at` | DATETIME | Soft delete |

### 2.7 `campana_envios`
Envíos individuales de cada campaña.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INTEGER PK | |
| `campana_id` | INTEGER FK → campanas | Campaña asociada |
| `cliente_id` | INTEGER FK → clientes | Cliente destinatario |
| `canal` | VARCHAR(20) | `whatsapp` o `email` |
| `estado` | VARCHAR(20) | `pendiente`, `enviado`, `fallido` |
| `intentos` | INTEGER | Número de reintentos |
| `error_mensaje` | TEXT | Error si falló |
| `sent_at` | DATETIME | Fecha/hora de envío exitoso |
| `created_at` | DATETIME | |
| `updated_at` | DATETIME | |

### 2.8 `promociones`
Promociones con condiciones especiales.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | integer PK | |
| `nombre` | varchar | Nombre de la promoción |
| `descripcion` | text | Descripción |
| `tipo` | varchar | `bonificacion` o `multiplicador` |
| `valor` | numeric | Puntos extra o factor multiplicador |
| `condiciones` | text | JSON con condiciones (monto_minimo, etc.) |
| `fecha_inicio` | date | Inicio de vigencia |
| `fecha_fin` | date | Fin de vigencia |
| `prioridad` | integer | Orden de evaluación |
| `activa` | tinyint(1) | Si está activa |
| `created_at` | datetime | |
| `updated_at` | datetime | |

### 2.9 `usuarios`
Usuarios del panel del tenant (no confundir con `users` de MySQL).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | integer PK | |
| `nombre` | varchar | Nombre |
| `email` | varchar | Email |
| `username` | varchar | Usuario de login |
| `password` | varchar | Hash de contraseña |
| `rol` | varchar | `admin`, `supervisor`, `operario` |
| `activo` | tinyint(1) | Si está activo |
| `ultimo_acceso` | datetime | Último login |
| `created_at` | datetime | |
| `updated_at` | datetime | |

### 2.10 `whatsapp_logs`
Log de todos los mensajes WhatsApp enviados.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | integer PK | |
| `cliente_id` | integer FK → clientes | Cliente destinatario |
| `numero` | varchar | Número de teléfono |
| `evento` | varchar | Tipo: `canje`, `vencimiento`, `bienvenida`, `promocion`, `campana` |
| `mensaje` | text | Contenido del mensaje |
| `estado` | varchar | `enviado`, `fallido` |
| `codigo_respuesta` | varchar | Código HTTP de la API WhatsApp |
| `error_mensaje` | text | Error si falló |
| `created_at` | datetime | |
| `updated_at` | datetime | |

### 2.11 `webhook_inbox`
Log de webhooks recibidos por este tenant (copia local).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | integer PK | |
| `estado` | varchar | `procesado`, `omitido`, `error` |
| `cfe_id` | integer | Tipo de documento fiscal |
| `documento_cliente` | varchar | Documento del cliente |
| `puntos_generados` | numeric | Puntos generados |
| `motivo_no_acumulo` | varchar | Razón si no acumuló |
| `origen` | varchar | Adaptador usado |
| `mensaje_error` | text | Error si falló |
| `payload_json` | text | JSON original |
| `procesado_en` | datetime | Fecha de procesamiento |
| `created_at` | datetime | |
| `updated_at` | datetime | |

### 2.12 `actividades`
Log de auditoría de acciones en el sistema.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | integer PK | |
| `usuario_id` | integer FK → usuarios | Usuario que realizó la acción (null = sistema) |
| `accion` | varchar | Tipo: `login`, `canje_puntos`, `factura_procesada`, `ajuste_puntos`, etc. |
| `descripcion` | text | Descripción legible |
| `datos_json` | text | JSON con datos adicionales (IP, user_agent, etc.) |
| `created_at` | datetime | |
| `updated_at` | datetime | |

---

## 3. RELACIONES PRINCIPALES

### SQLite (Tenant)
```
clientes ──┬── facturas (cliente_id)
            ├── puntos_canjeados (cliente_id)
            ├── puntos_vencidos (cliente_id)
            └── whatsapp_logs (cliente_id)

campanas ──── campana_envios (campana_id)
              campana_envios ── clientes (cliente_id)

usuarios ──── actividades (usuario_id)
```

### MySQL (Global)
```
tenants ──── webhook_inbox_global (tenant_rut)
users ────── admin_logs (user_id)
```

---

## 4. FLUJO DE DATOS DE PUNTOS

```
Webhook e-Factura
    │
    ▼
PuntosService::procesarFactura()
    │
    ├─► facturas (INSERT: puntos_generados, fecha_vencimiento = now + días_config)
    ├─► clientes (UPDATE: puntos_acumulados += puntos_generados)
    └─► webhook_inbox (INSERT: registro del webhook)

Canje de Puntos
    │
    ├─► puntos_canjeados (INSERT: registro del canje)
    ├─► facturas (DELETE/UPDATE: FIFO, elimina facturas viejas primero)
    └─► clientes (UPDATE: puntos_acumulados -= puntos_canjeados)

Vencimiento Automático (cron diario 03:00)
    │
    ├─► facturas vencidas → puntos_generados = 0
    ├─► puntos_vencidos (INSERT: registro del vencimiento)
    └─► clientes (UPDATE: puntos_acumulados -= puntos_vencidos, min 0)

Notificación (cron diario 03:00, después de vencimiento)
    │
    ├─► Busca facturas con fecha_vencimiento entre hoy y hoy+7
    ├─► Usa puntos_acumulados del cliente (saldo REAL)
    └─► whatsapp_logs (INSERT: registro del mensaje)
```
