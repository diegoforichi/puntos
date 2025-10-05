# 📜 CHANGELOG

## 2025-10-02 (Tarde - Final)
- **Simplificación de reglas de acumulación**: eliminado toggle "Solo CI"; ahora existe únicamente "Excluir e-Facturas" (cuando está activo, las e-Facturas se registran pero no acumulan puntos).
- **Conversión de moneda**: sistema convierte USD a moneda base (configurable por tenant); monedas sin tasa se omiten automáticamente (`estado=omitido`, `motivo=moneda_sin_tasa`).
- **Migración global**: `webhook_inbox_global.estado` ahora acepta valor `omitido` además de `pendiente`/`procesado`/`error`.
- **UI mejorada**: montos de facturas muestran moneda explícita (ej. `$5.950,00 UYU`, `$119,00 USD`) para evitar ambigüedades.
- **Formulario de canje**: corregido bug donde datos del cliente desaparecían al buscarlo; ahora permanecen estables hasta confirmar canje.
- **Botones de prueba en SuperAdmin**: agregados modales para "Enviar email de prueba" y "Enviar WhatsApp de prueba" en la configuración global.
- **Notificaciones WhatsApp implementadas**: 4 plantillas fijas (bienvenida, canje, puntos por vencer, promociones) que se disparan automáticamente según flags del tenant.
- **Reporte diario por email**: comando `php artisan tenant:send-daily-reports` genera y envía resumen de actividad diaria (facturas, puntos, clientes nuevos) al email de contacto de cada tenant activo. Se ejecuta vía cron a las 8:00 AM.
- **Favicon**: agregado favicon SVG con degradado azul-morado y punto blanco central en ambos layouts (tenant y SuperAdmin).
- **Corrección de typos**: unificado nombre de campo `acumulacion_excluir_efacturas` en vista, controlador y modelo; eliminadas referencias a `acumulacion_solo_ci`.
- **Emulador mejorado**: soporta flags `--rut`, `--api-key`, `--moneda`, `--doc-mode`, `--cfeid`, `--monto` para todos los escenarios de prueba.

## 2025-10-02 (Mañana)
- Regeneración controlada del tenant demo (`000000000016`): backup, borrado, `InitialDataSeeder`, `tenant:setup-database`, `tenant:migrate` y `TenantUserSeeder`.
- Validación end-to-end de webhook con emulador (CI 101, RUT 111, nota de crédito 112) sobre el nuevo esquema; verificación en `webhook_inbox` y `webhook_inbox_global`.
- Script utilitario `scripts/delete_tenant.php` para borrar tenants (incluye logs globales y archivo SQLite).
- `scripts/check_tenant_data.php` extendido para consultar `webhook_inbox_global` y columnas reales.
- Confirmación de negativización de puntos para notas de crédito y regeneración de clientes demo.

## 2025-10-01
- Ajustes al `PuntosService` para soportar reglas por tenant (solo CI / excluir e-Facturas) y manejar notas de crédito (`CfeId` 102/112) restando puntos.
- Registro completo del webhook por tenant (`webhook_inbox`) con estado procesado/omitido, documento, puntos generados y payload.
- `webhook_inbox_global` ahora guarda `cfe_id`, documento, puntos y motivo de omisión para reporte centralizado.
- Nuevo formulario en Configuración del tenant para toggles de acumulación y actualización del Manual de Usuario.
- Emulador `scripts/emulador_webhook.php` soporta flags `--cfeid`, `--doc-mode`, `--monto` y muestra puntos recibidos en la respuesta.
- Migraciones actualizadas para `facturas` y `webhook_inbox` con columnas `cfe_id`, `acumulo`, `motivo_no_acumulo`, `puntos_generados`.

## 2025-09-30
- Añadido panel **SuperAdmin** (`/superadmin`) con:
  - Dashboard global, configuración SMTP/WhatsApp, gestión de tenants, webhook inbox global.
  - Middleware y guard específico (`superadmin.auth`, `superadmin.guest`).
  - Auditoría en tabla `admin_logs` y campos adicionales en `tenants` (facturas/puntos/último webhook).
- Generación de usuarios iniciales por tenant desde el panel (user/password con sufijo por RUT, login acepta usuario o email).
- Modal de edición de tenant reposicionado, tabla de tenants muestra URL directa y botón de copiar.
- Alta de tenant crea automáticamente la base SQLite, corre migraciones tenant y genera credenciales iniciales (sin depender de Doctrine DBAL).
- Botón “Crear usuarios iniciales” reinicializa base si falta y muestra estado/resultados.
- Portal de Autoconsulta con sesión aislada por tenant, placeholders neutros y actualización de contacto en línea.
- Generación de usuarios iniciales muestra siempre credenciales (suffix basado en los últimos 4 dígitos del RUT, p.ej. admin3328). 
- Consolidación de documentación (manual de usuario, deployment, checklist de tareas, README actualizado).
- Migraciones nuevas: `add_role_status_to_users`, `create_admin_logs_table`, `add_gestion_fields_to_tenants`, `add_username_to_tenant_users`.
- Seeders actualizados (`DatabaseSeeder`, `InitialDataSeeder`, `TenantUserSeeder`) para generar SuperAdmin y datos demo con métricas/usuarios.

## 2025-09-29
- Implementación completa del panel del comercio (dashboard, clientes, canjes, promociones, reportes, usuarios, configuración, portal público).
- Webhook único con Adapter Pattern (`EfacturaAdapter`) y `PuntosService` aplicando promociones, generando facturas, puntos y logs.
- Emulador de webhook (`scripts/emulador_webhook.php`) para pruebas locales.
- Documentación funcional inicial (manual, arquitectura, deployment) y guía de pruebas.

---

> Para cambios previos y material histórico, consultar `docs/archive/` y commits anteriores.
