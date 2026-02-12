# Contexto Técnico del Proyecto (para el asistente)

## Stack real del proyecto

- **Laravel**: 10.x (ver `app/composer.json`)
- **PHP**: 8.2+
- **BD Global (MySQL)**: tenants, users, system_config, jobs/failed_jobs, logs globales
- **BD por Tenant (SQLite)**: clientes, facturas, canjes, promociones, actividades, whatsapp_logs, etc.
- **Hosting**: compartido (cron mínimo cada 15 min; sin Node.js en servidor)

## Restricciones operativas (hosting compartido)

- **No ejecutar** `composer` / `npm` en el servidor: subir `vendor/` y assets compilados.
- Cron típico:
  - `*/15 * * * * php artisan schedule:run`
  - `*/15 * * * * php artisan queue:work --queue=campanas --stop-when-empty ...`

## Puntos críticos del dominio

- **WhatsApp**: todos los envíos masivos deben pasar por Jobs con delay (“dosificación”) para evitar bloqueos del token.
- **Campañas**: en SQLite los IDs colisionan entre tenants → los Jobs deben recibir `tenantId` (no escanear todos los SQLite buscando por ID).
- **SQLite**: sirve para este hosting, pero hay límites (concurrencia y límites de variables en inserts masivos). Evitar inserts gigantes; usar chunks.

## Archivos clave

- `app/app/Jobs/EnviarNotificacionWhatsApp.php`
- `app/app/Jobs/EnviarCampanaJob.php`
- `app/app/Jobs/ProcesarEnvioCampana.php`
- `app/app/Console/Commands/NotifyExpiringPoints.php`
- `app/app/Console/Commands/TenantMaintenanceDaily.php`
- `docs/PLAN_MEJORAS_FUTURAS.md` (estado + delays)

