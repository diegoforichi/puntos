# Plan de Mejoras - Sistema de Puntos
**Ultima actualizacion:** 11/02/2026 | **Version:** 2.1

---

## 1. ESTADO ACTUAL DEL SISTEMA

### Funcionalidades Operativas
- Multi-tenancy (MySQL global + SQLite por tenant)
- Webhook e-Factura con adaptadores (alta y cancelacion)
- Campanias masivas (WhatsApp/Email) con Jobs asincronos y delays
- Notificaciones asincronas dosificadas (canjes, vencimiento, bienvenida, promociones)
- API de puntos (consulta y canje con Bearer token)
- Compactacion automatica de SQLite (semanal)
- Edicion y duplicacion de campanias
- Promociones con condiciones (bonificacion, multiplicador)
- Reportes con filtros avanzados, busqueda por cliente y exportacion CSV
- Portal publico de autoconsulta
- Vencimiento automatico de puntos (cron diario)

### Configuracion del Servidor
- **Servidor:** cPanel hosting compartido
- **PHP Timezone sistema:** `America/Boise` (MST -07:00)
- **Laravel Timezone:** `UTC` (delays y Jobs usan tiempos relativos, no afecta)
- **Cron:** Cada 15 minutos ejecuta `schedule:run` + `queue:work`
- **Tareas diarias (03:00):** `puntos:expirar` -> `puntos:notificar-vencimiento` -> `tenant:send-daily-reports`
- **Compactacion SQLite:** Lunes 04:00

---

## 2. CORRECCIONES REALIZADAS (todas aplicadas)

| # | Problema | Solucion | Fecha |
|---|----------|----------|-------|
| 1 | WhatsApp bloqueado por envios masivos sin delay | Delays en todos los Jobs (3-8 seg) y progresivo entre mensajes | 30/11/2025 |
| 2 | Filtro de fechas en reporte facturas no incluia dia completo | `whereDate()` en lugar de comparacion directa | 30/11/2025 |
| 3 | Reporte actividades limitado a 500 sin paginacion | `paginate(50)` con paginacion completa | 11/02/2026 |
| 4 | Notificacion de vencimiento enviaba monto incorrecto | Usa `puntos_acumulados` (saldo real) en vez de `SUM(puntos_generados)` | 11/02/2026 |
| 5 | Autoconsulta usaba columna inexistente `puntos_disponibles` | Cambiado a `puntos_generados` | 11/02/2026 |
| 6 | Comando de vencimiento enviaba 300+ mensajes de golpe sin delay | Usa Jobs con delay progresivo de 8 seg | 11/02/2026 |
| 7 | Bienvenida a nuevos clientes era sincrona | Usa Job asincrono con delay | 11/02/2026 |
| 8 | Reportes sin filtro por cliente | Busqueda por nombre/documento en canjes, facturas y clientes | 11/02/2026 |
| 9 | No se podia ver historial completo de canjes por cliente | Nueva vista `/clientes/{id}/canjes` con paginacion | 11/02/2026 |
| 10 | Editar cliente registraba actividad como "cliente_creado" | Nueva accion `ACCION_CLIENTE_ACTUALIZADO` | 11/02/2026 |
| 11 | Texto "500 actividades" en vista actividades (residuo) | Muestra total real con paginacion | 11/02/2026 |
| 12 | Riesgo de mezclar tenants en campañas por colision de `campanaId` | `EnviarCampanaJob` recibe `tenantId` y deja de escanear todos los SQLite | 11/02/2026 |
| 13 | Error SQLite `too many SQL variables` al crear campañas grandes | Insert de `campana_envios` en chunks de 100 en `store()` | 11/02/2026 |
| 14 | Campañas podían disparar ráfagas de jobs en cola | Delay progresivo 8s al despachar `ProcesarEnvioCampana` desde `EnviarCampanaJob` | 11/02/2026 |

---

## 3. COMPORTAMIENTO DE ENVIOS WHATSAPP

Todos los envios de WhatsApp pasan por el sistema de Jobs con delays para evitar bloqueos de Meta.

| Tipo | Delay Progresivo | Delay Interno (Job) | Total Entre Mensajes |
|------|------------------|---------------------|----------------------|
| Canjes | - | 3-6 seg | ~3-6 seg (esporadico) |
| Promociones masivas | 5 seg | 3-6 seg | ~8-11 seg |
| Campanias | 8 seg | 4-7 seg | ~12-15 seg |
| Puntos por vencer | 8 seg | 3-6 seg | ~11-14 seg |
| Bienvenida | 3-8 seg | 3-6 seg | ~6-14 seg (esporadico) |
| Test manual (config) | - | - | 1 mensaje, sin delay |

**Tiempos estimados:**
- 100 mensajes de promocion: ~15 min
- 100 envios de campania: ~20-25 min
- 300 notificaciones de vencimiento: ~40 min

---

## 4. MEJORAS FUTURAS PENDIENTES

### 4.1 Migracion a MySQL
**Prioridad:** Media (cuando haya tenants con alto volumen concurrente)

SQLite tiene limitaciones de concurrencia (bloqueo de archivo completo). La estrategia recomendada es Single Database con `tenant_id` usando Global Scopes de Laravel.

**Archivos:** Migraciones nuevas, todos los modelos de tenant, servicios

---

## 5. ARCHIVOS MODIFICADOS POR DEPLOY

### 11/02/2026 - Hotfix Campañas (anti-cruce tenants + chunk insert)
| Archivo | Cambio |
|---------|--------|
| `app/Jobs/EnviarCampanaJob.php` | Recibe `tenantId`, evita escaneo de tenants y despacha envíos con delay progresivo |
| `app/Http/Controllers/CampanaController.php` | Dispatch con `tenantId` + insert de `campana_envios` en chunks de 100 |
| `app/Console/Commands/ProcesarCampanasProgramadas.php` | Dispatch con `tenantId` para campañas programadas |

### 11/02/2026 - Deploy Febrero v3 (Mejora Reportes)
| Archivo | Cambio |
|---------|--------|
| `app/Http/Controllers/ReporteController.php` | Filtro por cliente en canjes y facturas, busqueda en clientes, estadisticas |
| `app/Http/Controllers/ClienteController.php` | Nueva vista canjes por cliente, fix accion auditoria |
| `app/Models/Actividad.php` | Nueva constante ACCION_CLIENTE_ACTUALIZADO |
| `resources/views/reportes/canjes.blade.php` | Filtros: cliente, tipo (canje/ajuste), estadisticas del periodo |
| `resources/views/reportes/facturas.blade.php` | Filtro cliente, resumen estadistico (monto, puntos) |
| `resources/views/reportes/clientes.blade.php` | Campo de busqueda por nombre/documento/email |
| `resources/views/reportes/actividades.blade.php` | Fix texto 500, paginacion, filtros nuevos |
| `resources/views/clientes/show.blade.php` | Links "Ver todos" en facturas y canjes |
| `resources/views/clientes/canjes.blade.php` | **NUEVA** vista historial completo canjes por cliente |
| `resources/views/clientes/facturas.blade.php` | **NUEVA** vista historial completo facturas por cliente |
| `routes/web.php` | Nueva ruta `/clientes/{id}/canjes` |

### 11/02/2026 - Deploy Febrero v2 (Dosificacion)
| Archivo | Cambio |
|---------|--------|
| `app/Jobs/EnviarNotificacionWhatsApp.php` | Agregados tipos VENCIMIENTO y BIENVENIDA |
| `app/Console/Commands/NotifyExpiringPoints.php` | Usa Jobs con delay 8 seg progresivo |
| `app/Services/PuntosService.php` | Bienvenida usa Job asincrono |

### 11/02/2026 - Deploy Febrero v1 (Correcciones)
| Archivo | Cambio |
|---------|--------|
| `app/Console/Commands/NotifyExpiringPoints.php` | Usa saldo real del cliente en vez de suma de facturas |
| `app/Http/Controllers/AutoconsultaController.php` | Corrige columna inexistente |

### 30/11/2025 - Deploy Diciembre
| Archivo | Cambio |
|---------|--------|
| `app/Jobs/EnviarNotificacionWhatsApp.php` | Delay 3-6 seg aleatorio |
| `app/Http/Controllers/PromocionController.php` | Delay progresivo 5 seg entre jobs |
| `app/Jobs/ProcesarEnvioCampana.php` | Sleep 4-7 seg aleatorio |
| `app/Http/Controllers/ReporteController.php` | Filtro fechas con whereDate |

---

## 6. SCRIPTS DE DIAGNOSTICO

### Ver estado de cola
```bash
php artisan tinker --execute="
echo 'Jobs pendientes: ' . DB::connection('mysql')->table('jobs')->count() . PHP_EOL;
echo 'Jobs fallidos: ' . DB::connection('mysql')->table('failed_jobs')->count() . PHP_EOL;
"
```

### Ver WhatsApp enviados hoy
```bash
php artisan tinker --execute="
\$t = App\Models\Tenant::on('mysql')->where('estado','activo')->first();
config(['database.connections.tenant'=>['driver'=>'sqlite','database'=>\$t->getSqlitePath(),'prefix'=>'']]);
\$envios = DB::connection('tenant')->table('whatsapp_logs')
    ->whereDate('created_at', date('Y-m-d'))
    ->selectRaw('evento, estado, COUNT(*) as total')
    ->groupBy('evento', 'estado')->get();
foreach(\$envios as \$e) { echo \$e->evento . ' | ' . \$e->estado . ' | ' . \$e->total . PHP_EOL; }
"
```

### Verificar timezone
```bash
php artisan tinker --execute="echo now()->format('Y-m-d H:i:s T');"
```

### Limpiar cache (despues de deploy)
```bash
php artisan config:clear; php artisan cache:clear; php artisan route:clear; php artisan view:clear
```

---

## 7. DOCUMENTACION DE REFERENCIA

| Documento | Contenido |
|-----------|-----------|
| `ESQUEMA_BASE_DATOS.md` | Modelo completo MySQL (global) y SQLite (tenant), relaciones, flujo de datos |
| `PLAN_MEJORAS_FUTURAS.md` | Este archivo: estado, correcciones, mejoras pendientes, deploys |
| `CHANGELOG.md` | Historial de cambios por fecha |
| `API_Puntos.md` | Documentacion de la API REST |

---

**Proxima revision:** Despues de validar que el token de WhatsApp se recupera y los envios dosificados funcionan correctamente
