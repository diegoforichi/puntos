# Arquitectura Técnica – Sistema de Puntos
**Fecha actualización:** 02/10/2025

---

## 1. Visión General

- **Framework:** Laravel 10 (PHP 8.1+).
- **Arquitectura:** multi-tenant “database-per-tenant”. MySQL contiene metadatos globales; cada tenant tiene SQLite aislado.
- **Flujos principales:**
  1. **Webhook** → Adapter → `PuntosService` → MySQL + SQLite (facturas/puntos/logs).
  2. **Panel SuperAdmin** (`/superadmin`) → Operaciones globales (configuración, tenants, webhooks).
  3. **Panel Tenant** (`/{rut}`) → Gestión operativa (clientes, canjes, promociones, reportes, portal público).

---

## 2. Bases de Datos

### 2.1 MySQL (`puntos_main`)
| Tabla | Propósito |
|-------|-----------|
| `users` | Usuarios globales (rol `superadmin`, columnas `role`, `status`). |
| `tenants` | Comercios registrados: `rut`, `nombre_comercial`, `api_key`, `estado`, `sqlite_path`, métricas (`facturas_recibidas`, `puntos_generados_total`, `ultimo_webhook`). |
| `system_config` | Configuraciones globales (claves `email`, `whatsapp`, `retencion_datos`) almacenadas en JSON (encriptadas cuando corresponde). |
| `webhook_inbox_global` | Log de todas las peticiones al webhook (tenant, estado: `pendiente`/`procesado`/`error`/`omitido`, http_status, cfe_id, puntos generados, motivo de omisión, payload). |
| `admin_logs` | Auditoría del SuperAdmin (usuario, acción, descripción, IP, metadata JSON). |

### 2.2 SQLite por Tenant (`storage/tenants/{rut}.sqlite`)
| Tabla | Propósito |
|-------|-----------|
| `clientes` | Datos del cliente (documento, nombre, contacto, puntos, última actividad). |
| `facturas` | Registros de facturas procesadas (monto, puntos, promoción aplicada, fechas, `cfe_id`, `acumulo`, `motivo_no_acumulo`, payload completo). |
| `puntos_canjeados` | Historial de canjes (cliente, puntos, usuario que autorizó, FIFO aplicado). |
| `puntos_vencidos` | Historial de puntos eliminados por fecha de vencimiento. |
| `promociones` | Reglas de promociones (`tipo`, `valor`, `condiciones` JSON, `prioridad`, `activa`). |
| `usuarios` | Usuarios del tenant (roles `admin`, `supervisor`, `operario`; campos `username`, `email` opcional, `password`, `rol`, `activo`). |
| `actividades` | Auditoría interna (usuario, acción, descripción, datos JSON). |
| `configuracion` | Parámetros locales (puntos por pesos, días de vencimiento, contacto, eventos WhatsApp, reglas de acumulación, moneda base y tasa USD). |
| `webhook_inbox` | Log local del webhook para el tenant (estado, `cfe_id`, documento, puntos generados u omitidos, motivo, payload). |
| `whatsapp_logs` | Registro de notificaciones enviadas/fallidas (preparado para futuras integraciones reales). |

> La autenticación del tenant utiliza `username` o `email`. Por defecto se generan usuarios iniciales con `username` y contraseñas temporales desde el panel SuperAdmin.

> Migraciones para MySQL están en `database/migrations`. Las migraciones del tenant (`database/migrations/tenant/`) se ejecutan automáticamente al crear un tenant o al regenerar usuarios.

---

## 3. Autenticación y Middleware

### 3.1 Guards
- **`superadmin` (session + `users`):** login `/superadmin/login`. Middleware: `superadmin.guest`, `superadmin.auth`.
- **Tenants (custom session):** login `/ {rut} /login`. Middleware: `tenant` (inyecta tenant en request), `auth.tenant` (valida sesión), `role` (autorización por rol).

### 3.2 Middleware Clave
| Middleware | Función |
|------------|---------|
| `IdentifyTenant` | Determina el tenant desde la URL (`{rut}`), configura conexión SQLite temporal. |
| `AuthenticateTenant` | Valida sesión (`session('usuario_id')`) y comparte `usuario`/`tenant` en vistas. |
| `CheckRole` | Restringe acciones por roles (`admin`, `supervisor`, `operario`). |
| `SuperAdminAuthenticate` | Usa `Auth::guard('superadmin')`, verifica rol y estado activo. |
| `SuperAdminRedirectIfAuthenticated` | Evita que un superadmin logueado acceda al login. |
| `SuperAdminLogAction` | Registra cada acción relevante del panel global en `admin_logs`. |

---

## 4. Procesamiento de Webhook

1. **Endpoint:** `POST /api/webhook/ingest`.
2. **Seguridad:** Header `Authorization: Bearer {api_key}`. El payload debe incluir el `rut` del emisor.
3. **Procesamiento:**
   - `WebhookController@ingest`
     - Valida API Key y estado del tenant.
     - Lodging en `webhook_inbox_global`.
     - Selecciona adapter adecuado (`EfacturaAdapter::matches`).
     - Convierte payload a `StandardInvoiceDTO` (incluye `cfe_id`, tipo documento cliente y payload completo).
     - Ejecuta `PuntosService::procesarFactura()`.
   - `PuntosService`
     - Configura conexión SQLite del tenant.
     - Determina reglas de acumulación (`excluir e-Facturas`) y signo por `CfeId` (notas de crédito restan puntos).
     - Convierte moneda extranjera (USD) a moneda base según tasa configurada; omite monedas sin tasa.
     - Registra factura aunque no acumule, guardando motivo (`excluir_efacturas`, `moneda_sin_tasa`) y payload.
     - Actualiza cliente y loguea en `webhook_inbox` el estado procesado/omitido.
     - Notifica bienvenida a clientes nuevos (si flag activo).
     - Retorna puntos generados y motivo en la respuesta.
4. **Respuesta estándar:** JSON con `status`, puntos generados, saldo actual y mensajes de error si aplica.
5. **Logs locales:** Inserción en `webhook_inbox` (SQLite) con estado (`procesado`, `error`, etc.).

---

## 5. Componentes Principales

| Componente | Ubicación | Descripción |
|------------|-----------|-------------|
| `PuntosService` | `app/Services/PuntosService.php` | Lógica central de facturación, puntos, promociones, registros FIFO. |
| `EfacturaAdapter` | `app/Adapters/EfacturaAdapter.php` | Adapter para JSON de eFactura (mapea a DTO estándar). |
| `StandardInvoiceDTO` | `app/DTOs/StandardInvoiceDTO.php` | DTO utilizado por el servicio para procesar facturas indiferentemente del origen. |
| `SuperAdminController` | `app/Http/Controllers/SuperAdmin/SuperAdminController.php` | Dashboard global, configuración, tenants, webhooks. |
| `AuthController` (tenant) | `app/Http/Controllers/AuthController.php` | Login/logout para usuarios del tenant (sesiones en SQLite). |
| `ConfiguracionController` | `app/Http/Controllers/ConfiguracionController.php` | Configuración del tenant (puntos, vencimiento, contacto, WhatsApp). |
| `PromocionController` | `app/Http/Controllers/PromocionController.php` | CRUD de promociones. |
| `WhatsAppService` | `app/Services/WhatsAppService.php` | Envío de mensajes WhatsApp usando config global; registra en `whatsapp_logs`. |
| `NotificacionService` | `app/Services/NotificacionService.php` | Plantillas y disparadores de notificaciones (bienvenida, canje, vencimiento, promociones). |

---

## 6. Frontend

- **Layouts:**
  - `resources/views/layouts/app.blade.php`: layout del panel del comercio (sidebar + navbar + responsive).
  - `resources/views/superadmin/layout.blade.php`: layout SuperAdmin con sidebar global.
- **Estilos:** Bootstrap 5 desde CDN (única dependencia externa permitida según preferencia del usuario) + estilos en línea específicos.
- **JS:** Vanilla JS para interacción (por ejemplo, toggles del sidebar y operaciones AJAX en canje).

---

## 7. Scripts y Utilidades

- `scripts/emulador_webhook.php`: envía solicitudes simuladas con payload `hookCfe.json`; parámetros opcionales para escenarios de error (RUT incorrecto, API Key errónea, falta de teléfono).
- Comandos Artisan personalizados:
  - `tenant:setup-database {rut}` → genera archivo SQLite y ejecuta migraciones del tenant.
  - `tenant:query {rut}` (QueryTenantData) → inspector básico de datos del tenant (clientes, puntos, facturas).
  - `tenant:send-daily-reports` → envía resumen diario de actividad por email a todos los tenants activos (ejecutar vía cron a las 8:00 AM).

---

## 8. Seguridad y Auditoría

- Contraseñas hashed con `bcrypt` (tanto `users` como `usuarios`).
- Configuraciones sensibles (`system_config`) encriptadas con `Crypt::encryptString`.
- Auditoría: `admin_logs` (SuperAdmin) + `actividades` (por tenant).
- Rate limiting configurable (por defecto se usa el throttling estándar de Laravel, puede ajustarse si se requiere).

---

## 9. Flujos Resumidos

1. **Alta tenant**:
   - SuperAdmin crea tenant → genera API Key → el sistema crea/ejecuta migraciones tenant en SQLite → usuarios iniciales auto-generados (pueden regenerarse desde el panel o via `TenantUserSeeder`).
2. **Factura entrante**:
   - Webhook recibe JSON → Adapter → PuntosService → actualiza cliente/factura → log global y local.
3. **Canje**:
   - Usuario del tenant (Admin/Supervisor) usa módulo Canjear → sistema aplica FIFO → registra en `puntos_canjeados` + `actividades` + genera cupón → notifica al cliente por WhatsApp (si flag activo).
4. **Promoción**:
   - Admin configura promoción → guardada en SQLite → `PuntosService` evalúa promociones activas por prioridad en cada factura.
5. **Portal público**:
   - Cliente ingresa documento → consulta `clientes` y `facturas` activas → opcionalmente actualiza contacto.

---

## 10. Documentación Complementaria
- `README.md`: estado general, accesos, quick start.
- `MANUAL_USUARIO.md`: guía operativa (SuperAdmin + tenants).
- `MANUAL_DEPLOYMENT.md`: despliegue, optimización, backups.
- `docs/CHECKLIST_TAREAS.md`: lista de verificación previa a entregas.

---

**© 2025 Sistema de Puntos** – Documento técnico para nuevos desarrolladores y handover entre equipos.
