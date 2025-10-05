# ✅ Checklist de Tareas y Pruebas
**Fecha:** 30/09/2025 • **Sistema:** Multitenant de Puntos

---

## 1. Preparación / Local
- [ ] `php artisan migrate` (asegura columnas `role`, `status`, métricas tenants y migraciones SQLite actualizadas).
- [ ] `php artisan db:seed` (SuperAdmin + tenant demo + usuarios demo con username/contraseña).
- [ ] `php artisan tenant:setup-database {RUT}` para cada tenant nuevo.
- [ ] Revisar `.env` (DB principal, `TENANT_DB_PATH`, `APP_URL`).

## 2. Acceso SuperAdmin
- [ ] Login `/superadmin/login` con `superadmin@puntos.local / superadmin123`.
- [ ] Dashboard muestra métricas (aunque sea `0`).
- [ ] Configuración global: guardar cambios sin error (contraseña/token opcional).
- [ ] Crear tenant de prueba → verificar mensaje con credenciales (adminXYZ / Admin123!, etc.).
- [ ] Botón “Crear usuarios iniciales” regenera usuarios (mensaje con credenciales actualizadas, sin errores).
- [ ] Webhook inbox lista registros (usar emulador de webhook si no hay datos).

## 3. Acceso Tenant Demo (`/000000000016`)
- [ ] Login con usernames: `admin1234`, `supervisor1234`, `operario1234` (o sufijo según RUT) y contraseñas generadas.
- [ ] Sidebar visible y responsive (botón hamburguesa en móvil).
- [ ] Dashboard con estadísticas y actividad reciente (5 ítems).
- [ ] Listado de clientes paginado (10 por página) + buscador.
- [ ] Detalle de cliente muestra facturas, canjes, vencidos.
- [ ] Canje de puntos: búsqueda→botones rápidos→cupón generado.
- [ ] Promociones: crear/editar/toggle/elim. sin error.
- [ ] Reportes: filtros y exportación CSV (abrir en Excel para validar encoding).
- [ ] Gestión de usuarios: crear, editar, resetear contraseña, activar/desactivar.
- [ ] Configuración del tenant: guardar puntos, vencimiento, contacto, WhatsApp (sin romper portal).
- [ ] Configuración del tenant: activar/desactivar “Solo CI” y “Excluir e-Facturas” validando que el webhook registre omitidos cuando corresponde.
- [ ] Portal público (`/{rut}/consulta`): consulta existente o no encontrado sin 419, datos aislados por tenant, contacto actualiza y muestra confirmación.

## 4. Webhook / Procesamiento
- [ ] Ejecutar `php scripts/emulador_webhook.php` (o cURL) → checkear:
  - Registro en `webhook_inbox_global` y `webhook_inbox` del tenant.
  - Creación/actualización de cliente, facturas y puntos.
  - Aplicación automática de promociones (si corresponde).
  - Conversión de moneda: probar payload en USD (verificar que se convierta según tasa configurada y que monedas desconocidas sigan la regla elegida).
  - Flags de acumulación: probar `--doc-mode=ci|rut` y `--cfeid=101|111|102|112` validando puntos sumados/omitidos.
  - Notas de crédito (`cfeid=102` o `112`) restan puntos cuando procede.

## 5. Documentación / Entregables
- [ ] `README.md` actualizado (estado actual + accesos).
- [ ] `MANUAL_USUARIO.md` (SuperAdmin + tenant, login por usuario/email).
- [ ] `MANUAL_DEPLOYMENT.md` (resumen despliegue y scripts clave).
- [ ] `docs/ARQUITECTURA.md` revisado (estructura real).
- [ ] `CHANGELOG.md` con la última versión (panel SuperAdmin, usuarios iniciales, credenciales).

## 6. Post-Deployment
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
- [ ] Habilitar backup cron (MySQL + SQLite) y documentar en SuperAdmin (próxima fase).
- [ ] Revisar permisos (`storage/`, `bootstrap/cache/`).
- [ ] Configurar HTTPS (certificado) y firewall.
- [ ] Compartir instrucciones de acceso (SuperAdmin + tenant demo).

---

**Nota:** Si se detecta un problema inesperado (especialmente en migraciones/estructuras), documentarlo y notificar antes de continuar con nuevas fases.
