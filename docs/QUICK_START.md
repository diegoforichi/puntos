# Inicio Rápido

## 🚀 3 Pasos para Empezar

### 1. Leer Documentos Clave (15 min)

```
📖 Lectura obligatoria:
└─ docs/GENERAL_RULES.md (10 min)
└─ docs/AI_DEVELOPMENT_GUIDELINES.md (5 min)
```

### 2. Configurar Proyecto (5 min)

```bash
# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Base de datos
php artisan migrate
```

### 3. Empezar a Desarrollar

```bash
# Modo desarrollo
composer run dev

# O manualmente
php artisan serve
npm run dev
```

---

## 📋 Reglas Esenciales (memorizar)

### ❌ NUNCA:
1. Hardcodear textos en vistas
2. Ejecutar composer/npm en servidor
3. Usar SQLite en producción
4. JavaScript complejo sin justificar
5. Deploy sin tests

### ✅ SIEMPRE:
1. Usar `__('models.xxx')` para textos
2. Documentar con PHPDoc
3. Incluir tests
4. Mantener simple
5. MySQL en producción
6. Mantener listado de archivos a subir al hosting tras cada cambio

---

## 🎯 Atajos de Desarrollo

### Crear CRUD completo:

```bash
# 1. Modelo + Migración + Factory
php artisan make:model Product -mf

# 2. Controlador
php artisan make:controller ProductController --resource

# 3. Form Request
php artisan make:request StoreProductRequest

# 4. Test
php artisan make:test ProductTest

# 5. Agregar a models.php
# resources/lang/es/models.php
'product' => [
    'singular' => 'Producto',
    'plural' => 'Productos',
    'article' => 'el',
    'article_plural' => 'los',
],

# 6. Crear vistas con traducciones
# resources/views/products/index.blade.php
```

---

## 🧪 Verificación Rápida

```bash
# Tests
php artisan test

# Formateo
vendor/bin/pint

# Sin hardcode
grep -r "Productos\|Clientes\|Facturas" resources/views/
```

---

## 📚 Documentos por Situación

| Situación | Documento |
|-----------|-----------|
| Empezar proyecto nuevo | GENERAL_RULES.md |
| Usar asistente IA | AI_DEVELOPMENT_GUIDELINES.md |
| Hacer deploy | SECURITY_CHECKLIST.md |
| Ver stack del proyecto | CONTEXT.md |
| Cambiar nombre de modelo | i18n-rules.md |
| Duda sobre código | code-conventions.md |
| Archivos para subir al hosting | QUICK_START.md (sección "Archivos para deploy") |

---

## ⚡ Comandos Más Usados

```bash
# Desarrollo
composer run dev

# Tests
php artisan test

# Formateo
vendor/bin/pint

# Crear cosas
php artisan make:model Product -mf
php artisan make:controller ProductController --resource
php artisan make:request StoreProductRequest
php artisan make:test ProductTest

# Deploy (local)
npm run build
composer install --no-dev
php artisan config:cache

# Deploy (servidor)
php artisan migrate --force
chmod -R 755 storage/ bootstrap/cache/
```

---

## 🧪 Pruebas de Campañas (local)

1. **Preparación**
   - Define `QUEUE_CONNECTION=sync` para pruebas rápidas o `QUEUE_CONNECTION=database` y levanta el worker con `php artisan queue:work --queue=campanas --tries=3 --timeout=90`.
   - Limpia cachés: `php artisan optimize:clear`.
2. **Migrar bases de tenants**
   - Ejecuta `php artisan tenant:migrate {RUT_DEL_TENANT}` para cada archivo `.sqlite` en `storage/tenants`.
3. **Configurar canales**
   - Desde `/{tenant}/configuracion` activa/desactiva WhatsApp y Email y carga credenciales personalizadas si aplica.
   - Usa los botones "Enviar prueba" para verificar la configuración antes de crear campañas.
4. **Crear datos de prueba**
   - Crea clientes manualmente en `/{tenant}/clientes/crear` con teléfono y correo válidos, opcionalmente asigna puntos iniciales.
5. **Crear y disparar campaña**
   - Completa el formulario en `/{tenant}/campanas/crear`, guarda y usa "Enviar ahora".
   - Revisa el detalle en `/{tenant}/campanas/{id}` (últimos 50 envíos y totales exitosos/fallidos).
   - **Placeholders disponibles:** `{nombre}`, `{puntos}`, `{comercio}`, `{telefono}`, `{email}`, `{documento}`.
6. **Gestionar campañas**
   - **Pausar:** detiene campañas programadas (estado `pendiente` → `pausada`).
   - **Reanudar:** reactiva campañas pausadas (estado `pausada` → `pendiente`).
   - **Eliminar:** borra campañas en estados `pendiente` o `pausada` (soft delete).
   - **Enviar ahora:** despacha inmediatamente campañas `pendiente`, `pausada` o `fallida`.
7. **Probar programación**
   - Programa fecha/hora y luego ejecuta `php artisan campanas:procesar-programadas` cuando llegue el momento.

> 📂 Log de referencia: `storage/logs/laravel.log` para capturar errores SMTP/HTTP.

---

## 🗂️ Migraciones por Tenant

```
php artisan tenant:migrate 000000000016
php artisan tenant:migrate 010203010205
php artisan tenant:migrate 050154840013
```

> Repite el comando para cada tenant en local y hosting antes de probar campañas.

---

## 🎯 Checklist Diario

Al empezar a trabajar:
- [ ] Leer reglas si es primera vez
- [ ] Pull del repo
- [ ] Tests pasando
- [ ] Entorno local funcionando

Al terminar:
- [ ] Código documentado
- [ ] Tests incluidos
- [ ] Sin hardcode
- [ ] Commit descriptivo
- [ ] Documentación actualizada (CHANGELOG, QUICK_START, etc.)
- [ ] Lista de archivos para deploy completada (ver sección "Archivos para deploy")

---

**Tiempo total de setup**: ~20 minutos  
**Siguiente**: Desarrollar siguiendo las reglas 🚀

---

## 📦 Archivos para Deploy

Mantén este listado actualizado en cada entrega. Copia/pega el bloque y marca los archivos modificados:

```
### Archivos para subir al hosting (actualizado 2025-11-04)
- [ ] app/app/Console/Commands/ProcesarCampanasProgramadas.php
- [ ] app/app/Http/Controllers/CampanaController.php (✨ nuevos métodos: pause, resume, destroy)
- [ ] app/app/Http/Controllers/ClienteController.php (✨ nuevos métodos: create, store)
- [ ] app/app/Http/Controllers/ConfiguracionController.php (🔧 fix WhatsApp test)
- [ ] app/app/Jobs/EnviarCampanaJob.php
- [ ] app/app/Jobs/ProcesarEnvioCampana.php (✨ placeholders extendidos, validación email/teléfono)
- [ ] app/app/Mail/CampanaMail.php
- [ ] app/app/Models/Campana.php (✨ SoftDeletes, métodos helper de permisos)
- [ ] app/app/Models/CampanaEnvio.php
- [ ] app/app/Services/WhatsAppService.php
- [ ] app/database/migrations/tenant/2025_10_23_120400_create_campanas_tables.php
- [ ] app/database/migrations/tenant/2025_10_25_000000_update_campanas_tables.php
- [ ] app/database/migrations/tenant/2025_11_04_000000_add_soft_deletes_and_paused_to_campanas.php (🆕 soft deletes)
- [ ] app/resources/views/campanas/index.blade.php (✨ dropdown de acciones según estado)
- [ ] app/resources/views/campanas/create.blade.php
- [ ] app/resources/views/campanas/show.blade.php (✨ botones dinámicos de acción)
- [ ] app/resources/views/clientes/create.blade.php (🆕 formulario manual de clientes)
- [ ] app/resources/views/clientes/index.blade.php (✨ botón "Nuevo Cliente")
- [ ] app/resources/views/emails/campana.blade.php
- [ ] app/routes/web.php (✨ nuevas rutas: pause, resume, destroy campañas; create/store clientes)
- [ ] docs/CHANGELOG.md
- [ ] docs/QUICK_START.md
```

> 💡 Sugerencia: actualiza la lista al cerrar cada tarea para evitar omisiones durante el deploy.

---

## 📚 Documentos por Situación

