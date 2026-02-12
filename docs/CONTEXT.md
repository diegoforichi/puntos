# Contexto del Proyecto - Sistema de Puntos (Multi-Tenant)

## 🎯 Resumen Ejecutivo

Aplicación Laravel 10 para gestión de puntos por comercio (multi-tenant). Se integra con e-Factura por webhook, maneja canjes, campañas/promociones y notificaciones WhatsApp **dosificadas** para evitar bloqueos del proveedor.

---

## 📋 Información Rápida

### Tecnologías
- **Laravel**: 10.x (ver `app/composer.json`)
- **PHP**: 8.2+
- **BD Global**: MySQL (tenants, users, jobs, config global)
- **BD por Tenant**: SQLite (clientes, facturas, canjes, promociones, logs)
- **Frontend**: Blade

### Multi-tenancy (cómo funciona)
- La conexión **MySQL** es la “global”.
- Cada tenant tiene su archivo SQLite (`database/tenants/{RUT}.sqlite`).
- Al ejecutar lógica “por tenant”, el sistema ajusta `database.connections.tenant` apuntando al SQLite correspondiente.

---

## ⏱️ Cron / Scheduler / Colas (hosting compartido)

Limitación típica: cron mínimo cada 15 minutos. Por eso:
- `schedule:run` corre cada 15 minutos.
- `queue:work --queue=campanas --stop-when-empty` corre cada 15 minutos para procesar envíos dosificados por Jobs.

Comandos clave:
- `tenant:tareas-diarias`: “cron maestro” (expira puntos, notifica vencimientos, envía resumen diario).
- `puntos:notificar-vencimiento`: notifica por WhatsApp con delays (Jobs).
- Cola `campanas`: campañas y promociones masivas (Jobs con delay progresivo).

---

## 📁 Archivos/Áreas principales

- **Campañas / Cola**: `app/app/Jobs/EnviarCampanaJob.php`, `app/app/Jobs/ProcesarEnvioCampana.php`
- **WhatsApp**: `app/app/Jobs/EnviarNotificacionWhatsApp.php`, `app/app/Services/WhatsAppService.php`
- **Vencimientos**: `app/app/Console/Commands/ExpirePoints.php`, `app/app/Console/Commands/NotifyExpiringPoints.php`
- **Reportes**: `app/app/Http/Controllers/ReporteController.php` + `app/resources/views/reportes/`

---

## 🚨 Recordatorios Importantes

### SIEMPRE
- ✅ Envíos WhatsApp masivos: **solo por Jobs con delay** (dosificación)
- ✅ En hosting: subir `vendor/` y assets compilados (no ejecutar composer/npm)
- ✅ Antes de probar campañas: verificar cola `campanas` y que no queden jobs colgados

### NUNCA
- ❌ Enviar WhatsApp en loop sin cola/delay (riesgo de bloqueo del token)
- ❌ Asumir que IDs de campañas son globales (en SQLite colisionan entre tenants)

---

## 📚 Docs recomendados (del proyecto)

- `docs/ARQUITECTURA.md`
- `docs/ESQUEMA_BASE_DATOS.md`
- `docs/PLAN_MEJORAS_FUTURAS.md`
- `docs/CHANGELOG.md`

---

**Última actualización**: 2026-02-11

