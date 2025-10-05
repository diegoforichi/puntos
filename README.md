# 🎯 Sistema de Puntos - Plataforma Multi-Tenant

Sistema integral de gestión de programas de fidelización con integración automática vía webhook.

**Versión:** 1.3  
**Framework:** Laravel 10  
**PHP:** 8.2+  
**Bases de datos:** MySQL (global) + SQLite (por tenant)

---

## 📋 Características Principales

- ✅ **Multi-tenancy:** Aislamiento completo de datos por comercio (SQLite por tenant)
- ✅ **Integración e-Factura:** Webhook automático para procesar facturas
- ✅ **Notificaciones WhatsApp:** Eventos configurables por tenant
- ✅ **Email automatizado:** Reportes diarios por SMTP
- ✅ **Sistema FIFO:** Canje inteligente de puntos
- ✅ **Promociones dinámicas:** Multiplicadores, bonificaciones y descuentos
- ✅ **Portal público:** Autoconsulta de puntos sin login
- ✅ **Multi-moneda:** Conversión automática con tasas configurables
- ✅ **Reportes CSV:** Exportación de clientes, facturas, canjes
- ✅ **Compactación de BD:** Limpieza automática de registros antiguos
- ✅ **Expiración automática:** Descuento diario de puntos vencidos con historial (`puntos:expirar`)
- ✅ **Cron maestro:** Comando único para todas las tareas programadas (`tenant:tareas-diarias`)
- ✅ **Cupones PDF:** Generación de cupones con 2 copias en 1 hoja A4 (cliente + comercio)
- ✅ **Reimpresión:** Acceso a cupones históricos desde detalle del cliente

---

## 🚀 Inicio Rápido

### Requisitos
- PHP 8.2+
- Composer 2.x
- MySQL 8.0+ (producción) o SQLite (demo local)
- Extensiones PHP: PDO, SQLite, OpenSSL, Mbstring, JSON, DOM, GD, Fileinfo

### Instalación Local

```bash
# 1. Clonar repositorio (o extraer desde ZIP)
cd C:\xampp\htdocs\puntos

# 2. Instalar dependencias
cd app
composer install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=puntos_global
DB_USERNAME=root
DB_PASSWORD=

# 5. Ejecutar migraciones
php artisan migrate --seed

# 6. Iniciar servidor de desarrollo
php artisan serve
```

Acceso:
- **Landing:** http://localhost:8000/
- **SuperAdmin:** http://localhost:8000/superadmin/login
  - Usuario: `superadmin@puntos.local`
  - Contraseña: `superadmin123`

---

## 📂 Estructura del Proyecto

### Estructura en Local (Desarrollo)
```
C:\xampp\htdocs\puntos\
├── app\                    # Proyecto Laravel
│   ├── app\
│   │   ├── Http\Controllers\
│   │   ├── Models\
│   │   ├── Services\      # Lógica de negocio
│   │   ├── DTOs\          # Data Transfer Objects
│   │   ├── Adapters\      # Adaptadores de webhook
│   │   └── Console\Commands\
│   ├── config\
│   ├── database\
│   │   └── tenants\       # SQLite files por tenant
│   ├── public\            # Assets públicos
│   ├── resources\
│   │   └── views\
│   ├── routes\
│   ├── storage\
│   │   └── logs\
│   ├── .env               # Configuración local
│   └── artisan
├── docs\                  # Documentación técnica
├── MANUAL_USUARIO.md      # Manual completo de usuario
└── README.md              # Este archivo
```

### Estructura en Hosting (Producción)
```
public_html/website_63382ba2/
├── index.php              # ← De app/public/ (ajustado)
├── .htaccess              # ← De app/public/
├── favicon.svg            # ← De app/public/
├── assets\                # ← De app/public/assets/
├── app\
├── bootstrap\
├── config\
├── database\
│   └── tenants\           # SQLite por comercio
├── resources\
├── routes\
├── storage\
└── vendor\                # ← En raíz (ejecutar composer install --no-dev)
```

**Diferencia clave:** En hosting, `index.php` se mueve a la raíz y se ajustan sus rutas para apuntar a `vendor/autoload.php` y `bootstrap/app.php` directamente desde raíz.

---

## 🌐 Despliegue a Hosting

### 1. Preparación de Archivos

#### Archivos a subir (primera vez):
```
- app/               (completo, excepto node_modules)
- bootstrap/
- config/
- database/
- resources/
- routes/
- storage/
- vendor/            (ejecutar composer install --no-dev --optimize-autoloader)
- .htaccess          (de app/public/)
- index.php          (de app/public/, ajustado como arriba)
- favicon.svg        (de app/public/)
- assets/            (de app/public/assets/)
```

#### Archivos a actualizar (cambios incrementales):
```
- app/app/Http/Controllers/*.php
- app/app/Models/*.php
- app/app/Services/*.php
- app/app/Console/Commands/*.php
- app/resources/views/**/*.blade.php
- app/routes/*.php
- app/config/*.php
- app/.env (si cambia configuración)
```

### 2. Configuración del `.env` en Hosting

```ini
APP_NAME="Sistema de Puntos"
APP_ENV=production
APP_KEY=base64:... # Generar con: php artisan key:generate
APP_DEBUG=false
APP_URL=https://tudominio.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nombre_bd_hosting
DB_USERNAME=usuario_hosting
DB_PASSWORD=contraseña_hosting

# Webhook
WEBHOOK_DEBUG_ENABLED=false
WEBHOOK_DEBUG_TOKEN=

# SQLite Tenants
DB_TENANT_PATH=/home/usuario/public_html/website_63382ba2/database/tenants
```

### 3. Ajustes en `index.php` (hosting)

Estructura del `index.php` en la raíz del hosting:

```php
<?php
define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

### 4. Limpiar Cachés tras Deploy

```bash
cd /home/usuario/public_html/website_63382ba2
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

O manualmente: borrar archivos PHP de `bootstrap/cache/` (excepto `.gitignore`).

### 5. Permisos en Hosting

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 755 database/tenants
```

### 6. Configurar Cron Jobs

Se recomienda agendar **un único cron diario** que ejecute todas las tareas automáticas:

```bash
0 3 * * * cd /home/usuario/public_html/website_63382ba2 && php artisan tenant:tareas-diarias >> /dev/null 2>&1
```

El comando `tenant:tareas-diarias` ejecuta, en este orden:
- `puntos:expirar` → Descuenta puntos vencidos y los registra en `puntos_vencidos`.
- `puntos:notificar-vencimiento` → Envía WhatsApp a clientes con puntos a vencer.
- `tenant:send-daily-reports` → Envía email diario a cada tenant con el resumen del día.

> Opcionalmente, se pueden mantener cron jobs separados para cada comando si el hosting lo requiere.

---

## 🔧 Comandos Artisan Útiles

### Desarrollo
```bash
# Iniciar servidor local
php artisan serve

# Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ver rutas disponibles
php artisan route:list

# Consola interactiva
php artisan tinker
```

### Gestión de Tenants y Mantenimiento
```bash
# Tareas diarias consolidadas (cron maestro) - Recomendado
php artisan tenant:tareas-diarias

# Filtrar por tenant específico
php artisan tenant:tareas-diarias --tenant=123456789012

# Días de gracia extra antes de expirar puntos
php artisan tenant:tareas-diarias --grace-days=7

# Comandos individuales (si se necesitan por separado)
php artisan puntos:expirar --tenant=123456789012 --days=0
php artisan puntos:notificar-vencimiento --tenant=123456789012 --days=7
php artisan tenant:send-daily-reports
```

---

## 🗄️ Arquitectura de Bases de Datos

### MySQL (Global)
- `users` - SuperAdmins
- `tenants` - Comercios registrados
- `system_config` - Configuración global (Email SMTP, WhatsApp)
- `webhook_inbox_global` - Log de todos los webhooks recibidos
- `admin_logs` - Auditoría de acciones del SuperAdmin

### SQLite (Por Tenant)
Ubicación: `database/tenants/{RUT}.sqlite`

Tablas principales:
- `usuarios` - Usuarios del comercio (admin, supervisor, operario)
- `clientes` - Clientes del programa de puntos
- `facturas` - Facturas procesadas con puntos generados
- `puntos_canjeados` - Historial de canjes
- `puntos_vencidos` - Registro de puntos expirados
- `promociones` - Promociones activas/inactivas
- `configuracion` - Config del tenant (conversión, vencimiento, etc.)
- `webhook_inbox` - Webhooks procesados para este tenant
- `whatsapp_logs` - Log de mensajes WhatsApp enviados
- `actividades` - Log de acciones del tenant

---

## 📡 Integración Webhook

### Endpoint
```
POST https://tudominio.com/api/webhook/ingest
Authorization: Bearer {API_KEY_DEL_TENANT}
Content-Type: application/json
```

### Ejemplo de Payload (e-Factura)
```json
{
  "CfeId": 101,
  "Numero": 12345,
  "FecEmis": "2025-10-04",
  "Client": {
    "NroDoc": "12345678",
    "RznSoc": "Juan Pérez",
    "NroTel": "098123456",
    "Email": "cliente@example.com"
  },
  "Total": {
    "TotMntTotal": 1500.00,
    "TpoMoneda": "UYU"
  },
  "Emisor": {
    "RUT": "000000000016"
  }
}
```

### Pruebas con cURL
```bash
curl -X POST https://tudominio.com/api/webhook/ingest \
  -H "Authorization: Bearer tk_XXXXXXXXXXX" \
  -H "Content-Type: application/json" \
  -d @factura.json
```

### Pruebas en Local con Túneles

#### Con Cloudflare Tunnel (cloudflared)
```bash
cloudflared tunnel --url http://localhost:8000
# Usar la URL pública generada (*.trycloudflare.com)
```

#### Con ngrok
```bash
ngrok http 8000
# Usar la URL pública generada (*.ngrok.io)
```

---

## 🎫 Sistema de Cupones PDF

### Características
- **Formato:** 2 copias en 1 hoja A4 (297x210mm)
  - Copia CLIENTE (para presentar en caja)
  - Copia COMERCIO (archivo interno)
- **Contenido:** Código único, puntos canjeados, datos del cliente, autorización
- **Sin QR:** Diseño simplificado y compatible con cualquier impresora
- **Biblioteca:** Dompdf (Laravel wrapper `barryvdh/laravel-dompdf`)

### Rutas Disponibles
```php
// Ver cupón en pantalla
GET /{tenant}/puntos/cupon/{id}

// Descargar/imprimir PDF
GET /{tenant}/puntos/cupon/{id}/pdf
```

### Reimpresión
Los usuarios Admin/Supervisor pueden reimprimir cupones desde:
- Vista de detalle del cliente (`/{tenant}/clientes/{id}`)
- Historial de canjes (botón "Reimprimir")

---

## 🔐 Seguridad

### Producción
- ✅ `APP_DEBUG=false` en `.env`
- ✅ `APP_ENV=production`
- ✅ HTTPS habilitado
- ✅ API Keys únicas por tenant
- ✅ Contraseñas hasheadas (bcrypt)
- ✅ Validación de entrada en todos los endpoints
- ✅ CORS configurado en `config/cors.php`
- ✅ Rate limiting en rutas API

### Recomendaciones
- Rotar API Keys periódicamente
- Cambiar contraseña del SuperAdmin después de la instalación
- Mantener logs de auditoría (`admin_logs`, `actividades`)
- Backups regulares de MySQL y SQLite

---

## 📊 Monitoreo y Logs

### Logs de Laravel
```
storage/logs/laravel.log
```

### Logs de WhatsApp (por tenant)
```sql
SELECT * FROM whatsapp_logs
WHERE tenant_id = '{RUT}'
ORDER BY created_at DESC;
```

### Webhooks Recibidos (global)
```sql
SELECT * FROM webhook_inbox_global
WHERE estado = 'fallido'
ORDER BY created_at DESC;
```

### Puntos Vencidos (por tenant)
```sql
SELECT * FROM puntos_vencidos
ORDER BY fecha_vencimiento DESC;
```

---

## 🐛 Troubleshooting

### Error: "No se puede establecer conexión MySQL"
- Verificar credenciales en `.env`
- Confirmar que MySQL está corriendo
- En XAMPP: Iniciar módulo MySQL

### Error: "SQLSTATE[HY000]: General error: 1 no such table"
- Ejecutar: `php artisan migrate --force`
- Verificar que el archivo SQLite existe en `database/tenants/{RUT}.sqlite`

### Error: "500 Internal Server Error" en hosting
- Revisar `storage/logs/laravel.log`
- Verificar permisos de `storage/` y `bootstrap/cache/`
- Ejecutar: `php artisan config:clear && php artisan route:clear && php artisan view:clear`

### Error: "Class 'Barryvdh\DomPDF\ServiceProvider' not found"
- Verificar que `vendor/` está completo en el hosting
- Ejecutar: `composer install --no-dev --optimize-autoloader`
- Confirmar que existe `vendor/barryvdh/laravel-dompdf/`

### PDF del cupón no renderiza o sale en blanco
- Revisar extensiones PHP habilitadas: `mbstring`, `gd`, `dom`, `xml`, `fileinfo`
- Limpiar cachés de vistas: `php artisan view:clear`
- Verificar permisos de `storage/framework/views/`

### WhatsApp no envía mensajes
- Verificar config global en SuperAdmin → Configuración
- Usar botón "Enviar WhatsApp de prueba"
- Revisar `whatsapp_logs` del tenant
- Confirmar que el cliente tiene teléfono configurado

### Landing page muestra error 404
- Verificar que existe `resources/views/landing.blade.php`
- Ejecutar: `php artisan route:clear`
- Confirmar ruta en `routes/web.php`:
  ```php
  Route::get('/', function () {
      return view('landing');
  });
  ```

---

## 📚 Documentación Adicional

- **Manual de Usuario:** `MANUAL_USUARIO.md` - Guía completa para SuperAdmin y Tenants
- **Arquitectura Técnica:** `docs/ARQUITECTURA.md` - Diseño del sistema y decisiones técnicas
- **Guía para Agentes:** `docs/AGENTS.md` - Estándares de desarrollo y flujo de trabajo

---

## 🆕 Últimas Actualizaciones (v1.3)

### Funcionalidades Nuevas
- ✅ **Comando `puntos:expirar`:** Vencimiento automático de puntos con registro en `puntos_vencidos`
- ✅ **Comando maestro `tenant:tareas-diarias`:** Consolida expiración, notificaciones y reportes en un solo cron job
- ✅ **Cupones PDF rediseñados:** 2 copias (cliente + comercio) en 1 hoja A4
- ✅ **Reimpresión de cupones:** Botón en detalle del cliente para Admin/Supervisor
- ✅ **Límite en facturas activas:** Muestra solo las 10 más recientes/próximas a vencer en detalle del cliente
- ✅ **Optimización UI:** Eliminada redundancia de botones en vista de cupón generado

### Correcciones
- ✅ Error 404 en ruta PDF del cupón (faltaba definición en `routes/web.php`)
- ✅ Método `descargarCuponPdf` no encontrado en hosting (archivo no actualizado)
- ✅ Layout del PDF mejorado para compatibilidad con Dompdf (table-based, altura fija en mm)
- ✅ Botones duplicados en vista `cupon.blade.php` reorganizados en secciones primarias/secundarias

### Archivos Modificados (última sesión - 05/10/2025)
```
Nuevos:
- app/app/Console/Commands/ExpirePoints.php
- app/app/Console/Commands/TenantMaintenanceDaily.php

Modificados:
- app/app/Http/Controllers/PuntosController.php (método descargarCuponPdf)
- app/app/Http/Controllers/ClienteController.php (límite 10 facturas activas)
- app/resources/views/puntos/cupon_pdf.blade.php (rediseño completo)
- app/resources/views/puntos/cupon.blade.php (reorganización de botones)
- app/resources/views/clientes/show.blade.php (botón reimprimir, contador facturas)
- app/routes/web.php (ruta PDF del cupón)
- app/composer.json (barryvdh/laravel-dompdf)
- app/config/app.php (Dompdf service provider)
- README.md (este archivo)
- MANUAL_USUARIO.md (actualizado)
```

---

## 👥 Contribución

Para desarrollo adicional, revisar `docs/AGENTS.md` con estándares y mejores prácticas del proyecto.

---

## 📞 Soporte

Para consultas técnicas o reportes de bugs, contactar al administrador del sistema.

**Última actualización:** 05/10/2025  
**Versión:** 1.3  
**Licencia:** Propietario

