# Fase 1 - Núcleo del Sistema - COMPLETADA ✅

## Fecha: 2025-09-29

## 🎯 Resumen Ejecutivo

Se completó exitosamente la **Fase 1: Setup y Desarrollo del Núcleo** del Sistema de Puntos Multitenant. El webhook funciona correctamente, procesa facturas de eFactura, genera clientes automáticamente y calcula/acumula puntos.

**Estado:** 100% funcional y testeado ✅

---

## 📊 Infraestructura Creada

### Base de Datos Principal (MySQL): `puntos_main`

**Ubicación:** MySQL en XAMPP  
**Configuración .env:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=puntos_main
DB_USERNAME=root
DB_PASSWORD=
```

**Tablas creadas:**

1. **`tenants`** - Comercios/Clientes del sistema
   - Campos: id, rut, nombre_comercial, api_key, estado, sqlite_path, datos de contacto, formato_factura
   - Índices: rut (unique), api_key (unique), estado, created_at
   - SoftDeletes: sí

2. **`system_config`** - Configuración global del sistema
   - Campos: id, key, value (JSON), description
   - Registros iniciales:
     - `whatsapp`: configuración del servicio WhatsApp (token, URL, código país)
     - `email`: configuración SMTP (host, port, user, pass)
     - `retencion_datos`: política de eliminación de datos históricos
   - Índice: key (unique)

3. **`webhook_inbox_global`** - Log centralizado de webhooks
   - Campos: id, tenant_rut, estado, origen, http_status, mensaje_error, payload_json, procesado_en
   - Foreign key: tenant_rut → tenants.rut
   - Índices: tenant_rut, estado, created_at
   - Propósito: Dashboard del SuperAdmin

### Bases de Datos por Tenant (SQLite)

**Ubicación:** `app/storage/tenants/{rut}.sqlite`  
**Tenant demo:** `000000000016.sqlite`

**Tablas creadas en cada tenant:**

1. **`clientes`** - Clientes finales del comercio
   - Campos: id, documento (unique), nombre, telefono, email, direccion, puntos_acumulados, ultima_actividad
   - Índices: documento, puntos_acumulados, ultima_actividad

2. **`facturas`** - Facturas de referencia (puntos activos)
   - Campos: id, cliente_id, numero_factura, monto_total, moneda, puntos_generados, promocion_aplicada, payload_json, fecha_emision, fecha_vencimiento
   - Foreign key: cliente_id → clientes.id
   - Índices: cliente_id, fecha_emision, fecha_vencimiento

3. **`puntos_canjeados`** - Histórico de canjes
   - Campos: id, cliente_id, puntos_canjeados, puntos_restantes, concepto, autorizado_por
   - Foreign key: cliente_id → clientes.id

4. **`puntos_vencidos`** - Histórico de vencimientos
   - Campos: id, cliente_id, puntos_vencidos, motivo

5. **`configuracion`** - Parámetros del tenant
   - Registros iniciales:
     - `puntos_por_pesos`: 100 (configurable)
     - `dias_vencimiento`: 180 (configurable)
     - `contacto`: datos del comercio (nombre_comercial, telefono, direccion, email)
     - `eventos_whatsapp`: eventos activos (puntos_canjeados, puntos_por_vencer, etc.)

6. **`promociones`** - Campañas de puntos
   - Campos: id, nombre, tipo, valor, condicion (JSON), fecha_inicio, fecha_fin, activa
   - Tipos: multiplicador, puntos_extra, descuento_canje

7. **`usuarios`** - Usuarios del comercio
   - Campos: id, nombre, email (unique), password, rol, activo, ultimo_acceso
   - Roles: admin, supervisor, operario

8. **`actividades`** - Log de acciones
   - Campos: id, usuario_id, accion, descripcion, datos_json

9. **`webhook_inbox`** - Log local de webhooks del tenant
   - Campos: id, estado, origen, mensaje_error, payload_json, procesado_en

10. **`whatsapp_logs`** - Histórico de notificaciones
    - Campos: id, cliente_id, numero, evento, mensaje, estado, codigo_respuesta, error_mensaje

---

## 🔧 Componentes Implementados

### 1. Modelos (app/app/Models/)

**Tenant.php**
- Métodos:
  - `generarApiKey()`: genera API Key única con prefijo `tk_`
  - `getSqlitePath()`: retorna ruta al archivo SQLite
  - `isActivo()`: verifica si estado es 'activo'
  - `scopeActivos()`: scope para queries de tenants activos
- SoftDeletes habilitado

**SystemConfig.php**
- Métodos estáticos:
  - `get($key, $default)`: obtiene valor decodificado
  - `set($key, $value, $description)`: guarda/actualiza configuración
  - `getWhatsAppConfig()`: retorna config de WhatsApp
  - `getEmailConfig()`: retorna config de Email

### 2. Controladores (app/app/Http/Controllers/)

**Api/WebhookController.php**
- Endpoint: `POST /api/webhook/ingest`
- Headers requeridos: `Authorization: Bearer {api_key}`
- Flujo:
  1. Valida API Key
  2. Busca tenant por API Key
  3. Extrae RUT del payload y valida coincidencia
  4. Conecta a SQLite del tenant
  5. Obtiene adaptador según `formato_factura`
  6. Convierte payload a DTO estándar
  7. Procesa factura con `PuntosService`
  8. Registra en `webhook_inbox_global` (MySQL)
  9. Registra en `webhook_inbox` (SQLite del tenant)
  10. Retorna respuesta JSON con detalles
- Métodos privados:
  - `registrarWebhookGlobal()`: log en MySQL
  - `conectarTenant()`: configura conexión a SQLite
  - `obtenerAdaptador()`: factory de adaptadores
  - `registrarWebhookTenant()`: log en SQLite
  - `errorResponse()`: respuesta de error estandarizada
- Códigos de error:
  - `MISSING_API_KEY` (401)
  - `INVALID_API_KEY` (401)
  - `MISSING_RUT` (400)
  - `RUT_MISMATCH` (403)
  - `INVALID_FORMAT` (400)
  - `INTERNAL_ERROR` (500)

### 3. Servicios (app/app/Services/)

**PuntosService.php**
- Método principal: `procesarFactura(StandardInvoiceDTO $factura): array`
- Flujo:
  1. Obtiene o crea cliente (con teléfono y email si vienen)
  2. Lee configuración del tenant (puntos_por_pesos, dias_vencimiento)
  3. Calcula puntos base: `monto_total / puntos_por_pesos`
  4. Aplica promociones (TODO: por ahora no implementado)
  5. Calcula fecha de vencimiento
  6. Guarda factura de referencia en tabla `facturas`
  7. Actualiza `puntos_acumulados` del cliente
  8. Registra actividad en log
- Métodos privados:
  - `obtenerOCrearCliente()`: busca por documento o crea nuevo
  - `obtenerConfiguracion()`: lee config del tenant
  - `registrarActividad()`: log de acciones
- Retorna: array con success, cliente, puntos_generados, puntos_totales, factura_id

### 4. Adaptadores (app/app/Adapters/)

**Contracts/InvoiceAdapter.php** (Interface)
- Métodos:
  - `matches(array $payload): bool`
  - `toStandard(array $payload): StandardInvoiceDTO`
  - `getName(): string`

**Adapters/EfacturaAdapter.php**
- Implementa: `InvoiceAdapter`
- Detecta formato eFactura: verifica `Emisor.RUC`, `Client`, `Total.TotMntTotal`
- Convierte a DTO estándar:
  - RUT emisor: `Emisor.RUC`
  - Número factura: `Serie-Numero`
  - Documento cliente: `Client.NroDoc`
  - Nombre cliente: `Client.RznSoc`
  - Teléfono: `Client.NroTel` (limpia y valida formato uruguayo)
  - Email: `Client.Email`
  - Monto: `Total.TotMntTotal`
  - Moneda: `Money.Valor`
  - Fecha: `FecEmis` (formatea a ISO 8601)
- Métodos privados:
  - `limpiarTelefono()`: valida formato 09XXXXXXX
  - `formatearFecha()`: convierte a ISO 8601

**DTOs/StandardInvoiceDTO.php**
- Estructura estándar de factura
- Propiedades:
  - rutEmisor, numeroFactura, documentoCliente, nombreCliente
  - telefonoCliente, emailCliente, montoTotal, moneda
  - fechaEmision, detalle, payloadOriginal
- Métodos:
  - `fromArray()`: crea desde array
  - `toArray()`: convierte a array

### 5. Comandos Artisan (app/app/Console/Commands/)

**SetupTenantDatabase.php**
- Comando: `php artisan tenant:setup-database {rut}`
- Propósito: inicializa base SQLite del tenant con todas las tablas
- Flujo:
  1. Busca tenant por RUT
  2. Crea archivo SQLite si no existe
  3. Configura conexión temporal
  4. Ejecuta migración de tablas del tenant
  5. Verifica tablas creadas
  6. Restaura conexión por defecto

**QueryTenantData.php**
- Comando: `php artisan tenant:query {rut}`
- Propósito: consultar datos del tenant sin herramientas externas
- Muestra:
  - Lista de clientes con puntos y teléfono
  - Lista de facturas con monto y puntos generados

### 6. Seeders (app/database/seeders/)

**InitialDataSeeder.php**
- Comando: `php artisan db:seed --class=InitialDataSeeder`
- Crea tenant demo:
  - RUT: `000000000016`
  - Nombre: `Demo Punto de Venta`
  - API Key: `test-api-key-demo`
  - Estado: `activo`
  - SQLite: `storage/tenants/000000000016.sqlite`
- Crea archivo SQLite vacío
- Muestra credenciales para usar con emulador

### 7. Migraciones (app/database/migrations/)

**MySQL (Principal):**
- `2025_09_29_210012_create_tenants_table.php`
- `2025_09_29_210020_create_system_config_table.php`
- `2025_09_29_210027_create_webhook_inbox_global_table.php`

**SQLite (Tenant):**
- `tenant/2025_09_29_000001_create_tenant_tables.php`
  - Crea las 10 tablas del tenant
  - Inserta configuración inicial

### 8. Rutas (app/routes/)

**api.php**
```php
Route::post('/webhook/ingest', [App\Http\Controllers\Api\WebhookController::class, 'ingest']);
```
- URL completa: `http://localhost:8000/api/webhook/ingest`
- Método: POST
- Headers: `Authorization: Bearer {api_key}`, `Content-Type: application/json`
- Body: JSON de eFactura

---

## 🧪 Herramientas de Prueba

### Emulador de Webhook (scripts/emulador_webhook.php)

**Uso básico:**
```bash
cd C:\xampp\htdocs\puntos
php scripts/emulador_webhook.php
```

**Opciones disponibles:**
- `--url=URL`: cambiar URL del webhook (default: localhost:8000)
- `--rut=RUT`: cambiar RUT del emisor (default: 000000000016)
- `--api-key=KEY`: cambiar API Key (default: test-api-key-demo)
- `--cantidad=N`: enviar N facturas (default: 1)
- `--sin-telefono`: simular cliente sin teléfono
- `--rut-incorrecto`: probar validación de RUT
- `--api-key-mala`: probar validación de API Key
- `--help`: mostrar ayuda

**Ejemplos:**
```bash
# Enviar 5 facturas
php scripts/emulador_webhook.php --cantidad=5

# Probar sin teléfono
php scripts/emulador_webhook.php --sin-telefono

# Probar error de seguridad
php scripts/emulador_webhook.php --api-key-mala
```

**Datos generados:**
- Cédulas uruguayas aleatorias (8 dígitos, formato 1XXXXXXX o 4XXXXXXX)
- Nombres y apellidos aleatorios
- Teléfonos uruguayos (09XXXXXXX)
- Montos entre $500 y $50,000
- Productos aleatorios de catálogo predefinido

---

## ✅ Pruebas Realizadas

### Test 1: Factura Simple
- **Enviado:** 1 factura, cliente 41970797, monto $4,703.10
- **Resultado:** ✅ Cliente creado, 47.031 puntos generados
- **Verificado:** MySQL (webhook_inbox_global) + SQLite (clientes, facturas)

### Test 2: Múltiples Facturas
- **Enviado:** 2 facturas adicionales
  - Cliente 16069052: $38,950.94
  - Cliente 47469505: $18,407.36
- **Resultado:** ✅ 2 clientes nuevos creados
  - 389.5094 puntos
  - 184.0736 puntos
- **Total sistema:** 3 clientes, 3 facturas, 620.61 puntos

### Test 3: Validaciones de Seguridad
- **API Key incorrecta:** ✅ Retorna 401 INVALID_API_KEY
- **RUT no coincide:** ✅ Retorna 403 RUT_MISMATCH
- **Cliente sin teléfono:** ✅ Procesa correctamente, telefono=null

---

## 📂 Estructura de Archivos Generados

```
puntos/
├── app/
│   ├── app/
│   │   ├── Adapters/
│   │   │   └── EfacturaAdapter.php ✅
│   │   ├── Console/
│   │   │   └── Commands/
│   │   │       ├── QueryTenantData.php ✅
│   │   │       └── SetupTenantDatabase.php ✅
│   │   ├── Contracts/
│   │   │   └── InvoiceAdapter.php ✅
│   │   ├── DTOs/
│   │   │   └── StandardInvoiceDTO.php ✅
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── Api/
│   │   │           └── WebhookController.php ✅
│   │   ├── Models/
│   │   │   ├── SystemConfig.php ✅
│   │   │   └── Tenant.php ✅
│   │   └── Services/
│   │       └── PuntosService.php ✅
│   ├── database/
│   │   ├── migrations/
│   │   │   ├── 2025_09_29_210012_create_tenants_table.php ✅
│   │   │   ├── 2025_09_29_210020_create_system_config_table.php ✅
│   │   │   ├── 2025_09_29_210027_create_webhook_inbox_global_table.php ✅
│   │   │   └── tenant/
│   │   │       └── 2025_09_29_000001_create_tenant_tables.php ✅
│   │   └── seeders/
│   │       └── InitialDataSeeder.php ✅
│   ├── routes/
│   │   └── api.php ✅ (modificado)
│   ├── storage/
│   │   ├── backups/ ✅ (directorio creado)
│   │   └── tenants/ ✅ (directorio creado)
│   │       └── 000000000016.sqlite ✅
│   └── .env ✅ (configurado)
├── scripts/
│   ├── emulador_webhook.php ✅
│   └── README.md ✅
├── 01_FUNCIONALIDAD_Y_REQUISITOS.md ✅
├── 02_ARQUITECTURA_TECNICA.md ❌ (pendiente)
├── 03_MIGRACION.md ✅
├── 06_MODULO_WHATSAPP.md ✅
├── INICIO_RAPIDO.md ✅
├── LIMITACIONES_HOSTING.md ✅
├── MAPA.md ✅
├── README.md ✅
└── FASE_1_COMPLETADA.md ✅ (este archivo)
```

---

## 🔐 Credenciales y Configuración Actual

### Tenant Demo
- **RUT:** 000000000016
- **Nombre:** Demo Punto de Venta
- **API Key:** test-api-key-demo
- **Estado:** activo
- **Base SQLite:** app/storage/tenants/000000000016.sqlite

### Base de Datos MySQL
- **Host:** 127.0.0.1
- **Puerto:** 3306
- **Database:** puntos_main
- **Usuario:** root
- **Contraseña:** (vacía)

### Configuración de Puntos (Tenant)
- **Puntos por pesos:** 100 (cada $100 = 1 punto)
- **Días de vencimiento:** 180 días
- **Eventos WhatsApp:** activos (puntos_canjeados, puntos_por_vencer)

### Sistema Global
- **WhatsApp:** inactivo (token vacío)
- **Email SMTP:** no configurado
- **Retención de datos:** 1 año

---

## 📋 Comandos Útiles de Referencia

### Servidor Laravel
```bash
cd C:\xampp\htdocs\puntos\app
php artisan serve
```

### Consultar Datos del Tenant
```bash
cd C:\xampp\htdocs\puntos\app
php artisan tenant:query 000000000016
```

### Enviar Factura de Prueba
```bash
cd C:\xampp\htdocs\puntos
php scripts/emulador_webhook.php --cantidad=5
```

### Ver Log Global (MySQL)
```bash
C:\xampp\mysql\bin\mysql.exe -u root puntos_main -e "SELECT id, tenant_rut, estado, origen, http_status, LEFT(payload_json,100) AS payload, created_at FROM webhook_inbox_global ORDER BY id DESC LIMIT 10;"
```

### Reset Completo (CUIDADO: Borra todo)
```bash
cd C:\xampp\htdocs\puntos\app
php artisan migrate:fresh
del storage\tenants\*.sqlite
php artisan db:seed --class=InitialDataSeeder
php artisan tenant:setup-database 000000000016
```

### Crear Nuevo Tenant
```sql
-- En MySQL
INSERT INTO tenants (rut, nombre_comercial, api_key, estado, sqlite_path, created_at, updated_at)
VALUES ('210010020030', 'Mi Comercio SA', 'tk_nuevo_api_key_aqui', 'activo', 'C:\xampp\htdocs\puntos\app\storage\tenants\210010020030.sqlite', NOW(), NOW());
```
```bash
# Crear base SQLite
cd C:\xampp\htdocs\puntos\app
php artisan tenant:setup-database 210010020030
```

---

## ⚠️ Limitaciones Conocidas (Fase 1)

1. **No hay interfaz web (panel):** Solo API webhook funcional
2. **No hay autenticación de usuarios:** Tabla `usuarios` existe pero no se usa
3. **Promociones no implementadas:** Estructura lista, lógica pendiente
4. **WhatsApp no funcional:** Módulo documentado pero no integrado
5. **Sin dashboard:** No hay visualización de estadísticas
6. **Sin reportes:** No hay exportación CSV/PDF
7. **Sin canje de puntos:** API no implementada
8. **Sin vencimiento automático:** Cron no configurado
9. **Sin portal público:** No hay autoconsulta de puntos
10. **Un solo adaptador:** Solo eFactura, `formato_factura` no se usa aún

---

## 🎯 Próximos Pasos (Fase 2)

### Prioridades Inmediatas

1. **Autenticación y Roles**
   - Login por tenant (`/{tenant}/login`)
   - Middleware de autenticación
   - Seeder de usuario admin inicial
   - Gestión de sesiones

2. **Dashboard Básico**
   - Vista principal con estadísticas
   - Métricas: total puntos, clientes activos, facturas del mes
   - Gráfico simple de evolución

3. **Gestión de Clientes**
   - Listar clientes con búsqueda
   - Ver detalle de cliente
   - Historial de facturas y canjes

4. **Sistema de Canje**
   - API para canjear puntos (total/parcial)
   - Validación de autorización (supervisor/admin)
   - Generación de cupón digital
   - Registro en `puntos_canjeados`

5. **Portal de Autoconsulta**
   - Ruta pública `/{tenant}/consulta`
   - Formulario: ingreso de documento
   - Vista de puntos disponibles
   - Captura opcional de teléfono

### Fase 2 Completa (Semana 5-7 según plan)

- Sistema de promociones funcional
- Reportes con exportación CSV
- Centro de notificaciones en panel
- Gestión de usuarios (CRUD)

---

## 📝 Notas Técnicas para Asistente IA

### Conexión a SQLite del Tenant
```php
// Siempre usar este patrón
config([
    'database.connections.tenant' => [
        'driver' => 'sqlite',
        'database' => $tenant->getSqlitePath(),
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]
]);

DB::purge('tenant');
DB::setDefaultConnection('tenant');

// Hacer queries...

// Restaurar conexión por defecto
DB::setDefaultConnection('mysql');
```

### Respuestas JSON Estandarizadas
```php
// Éxito
return response()->json([
    'success' => true,
    'message' => 'Mensaje descriptivo',
    'data' => [...],
    'timestamp' => now()->toIso8601String()
], 200);

// Error
return response()->json([
    'success' => false,
    'error' => 'Mensaje de error',
    'code' => 'ERROR_CODE',
    'timestamp' => now()->toIso8601String()
], 400);
```

### Obtener Configuración del Tenant
```php
// En SQLite del tenant (después de conectar)
$config = DB::table('configuracion')
    ->where('key', 'puntos_por_pesos')
    ->value('value');

$valor = json_decode($config, true)['valor'] ?? 100;
```

### Registrar Actividad
```php
DB::table('actividades')->insert([
    'usuario_id' => auth()->id() ?? null,
    'accion' => 'accion_realizada',
    'descripcion' => 'Descripción legible',
    'datos_json' => json_encode(['key' => 'value']),
    'created_at' => now(),
    'updated_at' => now()
]);
```

### Validar API Key en Middleware (futuro)
```php
$apiKey = $request->bearerToken();
$tenant = Tenant::where('api_key', $apiKey)
    ->where('estado', 'activo')
    ->first();

if (!$tenant) {
    abort(401, 'API Key inválida');
}
```

---

## 🔍 Debugging y Troubleshooting

### Ver Logs de Laravel
```bash
cd C:\xampp\htdocs\puntos\app
type storage\logs\laravel.log | more
```

### Verificar Estructura de SQLite
```bash
# Si tienes sqlite3 instalado
sqlite3 storage\tenants\000000000016.sqlite ".tables"
sqlite3 storage\tenants\000000000016.sqlite ".schema clientes"
```

### Verificar Conexión MySQL
```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "SHOW DATABASES;"
C:\xampp\mysql\bin\mysql.exe -u root puntos_main -e "SHOW TABLES;"
```

### Limpiar Caché de Laravel
```bash
cd C:\xampp\htdocs\puntos\app
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## ✅ Checklist de Fase 1

- [x] Laravel 10 instalado y configurado
- [x] Base de datos MySQL creada (`puntos_main`)
- [x] Migraciones de tablas principales ejecutadas
- [x] Tenant demo creado con API Key
- [x] Base SQLite del tenant inicializada
- [x] Webhook funcionando con validaciones
- [x] Adaptador eFactura implementado
- [x] Servicio de puntos funcional
- [x] Clientes creados automáticamente
- [x] Puntos calculados y acumulados correctamente
- [x] Logs duales (MySQL global + SQLite tenant)
- [x] Emulador de webhook funcional
- [x] Comandos Artisan de utilidad creados
- [x] Sistema probado con múltiples facturas
- [x] Documentación técnica completa

**Estado Final:** FASE 1 COMPLETADA AL 100% ✅

---

## 📞 Soporte para Próximo Asistente IA

Si otro asistente continúa el trabajo:

1. Leer este documento completo
2. Verificar que el servidor Laravel esté corriendo
3. Probar el webhook con el emulador
4. Consultar datos con `php artisan tenant:query 000000000016`
5. Revisar `MAPA.md` para estructura del proyecto
6. Seguir plan en `03_MIGRACION.md` (Fase 2)
7. Respetar límite de 1000 líneas por archivo
8. Mantener patrón de conexión SQLite documentado aquí
9. Actualizar `MAPA.md` con cada archivo nuevo
10. Probar cada funcionalidad antes de continuar

**Contacto técnico:** Toda la información está en los archivos `.md` del proyecto.
