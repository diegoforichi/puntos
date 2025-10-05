# Inicio Rápido - Sistema de Puntos

## Fecha: 2025-09-29

## 🎯 Estado Actual del Proyecto

**Fase Actual:** Preparación y Setup  
**Documentación:** ✅ 100% Completa  
**Código:** ⏳ En desarrollo

---

## 📁 Estructura Actual del Proyecto

```
puntos/
├── docs/                           # (futuro) Documentación
├── scripts/                        # ✅ Scripts de utilidad
│   ├── emulador_webhook.php       # Emulador de facturas
│   └── README.md                  # Documentación de scripts
├── 01_FUNCIONALIDAD_Y_REQUISITOS.md
├── 02_ARQUITECTURA_TECNICA.md
├── 03_MIGRACION.md
├── 06_MODULO_WHATSAPP.md
├── LIMITACIONES_HOSTING.md
├── MAPA.md
├── README.md
├── INICIO_RAPIDO.md              # Este archivo
├── hookCfe.json                   # JSON de referencia eFactura
├── codigoDemo.txt                 # Código Apps Script original
└── referencia para usar servicio whatsap.txt
```

---

## 🚀 Próximos Pasos

### 1. Inicializar Proyecto Laravel 10

```bash
# Desde la carpeta puntos/
composer create-project laravel/laravel app --prefer-dist

# Mover al directorio app/
cd app
```

### 2. Configurar Base de Datos

Editar `app/.env`:

```env
APP_NAME="Sistema de Puntos"
APP_URL=http://localhost:8000

# Base de datos principal (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=puntos_main
DB_USERNAME=root
DB_PASSWORD=

# Directorio de bases SQLite por tenant
TENANT_DB_PATH=storage/tenants
```

### 3. Crear Base de Datos MySQL

```sql
CREATE DATABASE puntos_main CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Instalar Dependencias Adicionales

```bash
# Laravel Sanctum para autenticación API
composer require laravel/sanctum

# Generador de PDFs (para reportes)
composer require barryvdh/laravel-dompdf
```

### 5. Crear Estructura de Directorios

```bash
# Crear directorio para bases SQLite de tenants
mkdir -p storage/tenants

# Crear directorio para backups
mkdir -p storage/backups

# Permisos (Linux/Mac)
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 6. Probar Emulador de Webhook

```bash
# Terminal 1: Iniciar servidor Laravel
php artisan serve

# Terminal 2: Probar emulador (fallará hasta implementar webhook)
php ../scripts/emulador_webhook.php --help
```

---

## 📋 Checklist de Setup Inicial

- [ ] Proyecto Laravel 10 creado
- [ ] Base de datos MySQL configurada
- [ ] Archivo `.env` configurado
- [ ] Dependencias instaladas
- [ ] Directorios de storage creados
- [ ] Servidor Laravel corriendo en `http://localhost:8000`
- [ ] Emulador de webhook probado

---

## 🔧 Fase 1: Desarrollo del Núcleo

### Tareas Inmediatas

1. **Migraciones Base**
   - [ ] Tabla `tenants` (MySQL)
   - [ ] Tabla `system_config` (MySQL)
   - [ ] Tabla `webhook_inbox_global` (MySQL)
   - [ ] Tablas por tenant (SQLite): clientes, usuarios, configuracion, etc.

2. **Middleware Multitenant**
   - [ ] `TenantMiddleware.php` - Resolución de tenant
   - [ ] Conexión dinámica a base SQLite

3. **Webhook Base**
   - [ ] Ruta `POST /api/webhook/ingest`
   - [ ] Validación de API Key
   - [ ] Tabla `webhook_inbox` en SQLite
   - [ ] Registro en `webhook_inbox_global` (MySQL)

4. **Adaptador eFactura**
   - [ ] Interface `InvoiceAdapter`
   - [ ] Clase `EfacturaAdapter`
   - [ ] DTO `StandardInvoiceDTO`

5. **Seed Inicial**
   - [ ] Comando `php artisan app:seed-inicial`
   - [ ] Crear SuperAdmin
   - [ ] Crear `system_config` por defecto
   - [ ] Crear tenant demo con API Key

### Validación Fase 1

Una vez completadas las tareas:

```bash
# 1. Ejecutar seed
php artisan app:seed-inicial

# 2. Probar webhook con emulador
php scripts/emulador_webhook.php --cantidad=3

# 3. Ver en MySQL que se registraron en webhook_inbox_global
mysql> SELECT * FROM webhook_inbox_global;

# 4. Ver en SQLite del tenant que se procesaron
sqlite3 storage/tenants/000000000016.sqlite
sqlite> SELECT * FROM webhook_inbox;
```

---

## 🧪 Testing Durante Desarrollo

### Pruebas Locales

```bash
# Enviar 1 factura
php scripts/emulador_webhook.php

# Enviar múltiples facturas
php scripts/emulador_webhook.php --cantidad=5

# Probar errores
php scripts/emulador_webhook.php --api-key-mala
php scripts/emulador_webhook.php --rut-incorrecto
php scripts/emulador_webhook.php --sin-telefono
```

### Pruebas con Cliente Real (eFactura)

```bash
# Opción 1: Usar ngrok para exponer local
ngrok http 8000
# Proporcionar URL al administrador de eFactura: https://abc123.ngrok.io/api/webhook/ingest

# Opción 2: Desplegar en hosting temporal
# Configurar en servidor remoto y dar URL real
```

---

## 📊 Monitoreo Durante Desarrollo

### Logs de Laravel

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Ver logs de webhook específicamente
tail -f storage/logs/webhook.log
```

### Bases de Datos

```bash
# Ver tenants registrados (MySQL)
mysql> SELECT rut, nombre_comercial, estado, api_key FROM tenants;

# Ver inbox global (MySQL)
mysql> SELECT id, tenant_rut, estado, created_at FROM webhook_inbox_global ORDER BY id DESC LIMIT 10;

# Ver inbox de un tenant (SQLite)
sqlite3 storage/tenants/000000000016.sqlite
sqlite> SELECT id, estado, created_at FROM webhook_inbox ORDER BY id DESC LIMIT 10;
```

---

## 🐛 Troubleshooting Común

### Error: "SQLSTATE[HY000] [1049] Unknown database"
**Solución:** Crear la base de datos MySQL manualmente
```bash
mysql -u root -p
CREATE DATABASE puntos_main;
```

### Error: "Permission denied" en storage/
**Solución:** Dar permisos correctos
```bash
chmod -R 775 storage
chown -R www-data:www-data storage  # Linux
```

### Error: "Class 'PDO' not found"
**Solución:** Instalar extensión PHP
```bash
# Ubuntu/Debian
sudo apt-get install php8.1-mysql php8.1-sqlite3

# Windows (XAMPP): Descomentar en php.ini
extension=pdo_mysql
extension=pdo_sqlite
```

### Emulador: "Error de conexión"
**Solución:** Verificar que Laravel está corriendo
```bash
php artisan serve
# Debe mostrar: Server running on [http://127.0.0.1:8000]
```

---

## 📚 Recursos Útiles

### Documentación del Proyecto
- **[README.md](README.md)** - Resumen general
- **[01_FUNCIONALIDAD_Y_REQUISITOS.md](01_FUNCIONALIDAD_Y_REQUISITOS.md)** - Especificaciones funcionales
- **[02_ARQUITECTURA_TECNICA.md](02_ARQUITECTURA_TECNICA.md)** - Arquitectura y adaptadores
- **[03_MIGRACION.md](03_MIGRACION.md)** - Plan de desarrollo por fases
- **[MAPA.md](MAPA.md)** - Registro de archivos del proyecto

### Scripts
- **[scripts/README.md](scripts/README.md)** - Documentación de scripts de prueba

### Referencias
- `hookCfe.json` - Estructura JSON de eFactura
- `codigoDemo.txt` - Código Apps Script original
- `referencia para usar servicio whatsap.txt` - Integración WhatsApp

### Repositorio
- GitHub: https://github.com/diegoforichi/puntos

---

## 🎯 Criterios de Éxito - Fase 1

Al finalizar la Fase 1, deberías poder:

1. ✅ **Enviar facturas** vía emulador y verlas registradas en `webhook_inbox`
2. ✅ **Ver datos en bandeja de entrada** del panel administrativo
3. ✅ **Procesar facturas** con el adaptador eFactura
4. ✅ **Generar puntos** basados en el monto de la factura
5. ✅ **Registrar clientes** automáticamente al recibir su primera factura
6. ✅ **Autenticar usuarios** por tenant con roles básicos
7. ✅ **Navegar** a `http://localhost:8000/{tenant}` y ver panel básico

---

## 💡 Notas Importantes

- **No hay dependencias externas de paquetes multitenant:** implementamos la resolución de tenant nosotros mismos
- **SQLite por tenant:** cada comercio tiene su propia base de datos aislada
- **MySQL para central:** tenants, configuración global, logs globales
- **Límite de 1000 líneas por archivo:** si un archivo crece mucho, dividir en múltiples clases/services
- **Emulador siempre disponible:** usar el script para pruebas sin depender del sistema de eFactura real

---

## 🚦 Estado de Desarrollo

| Fase | Estado | Progreso |
|------|--------|----------|
| Documentación | ✅ Completo | 100% |
| Scripts de Prueba | ✅ Completo | 100% |
| Fase 1: Núcleo | ⏳ Pendiente | 0% |
| Fase 2: Avanzadas | ⏳ Pendiente | 0% |
| Fase 3: Integraciones | ⏳ Pendiente | 0% |
| Fase 4: Testing | ⏳ Pendiente | 0% |

**Último Update:** 2025-09-29
