# Sistema de Puntos Multitenant

Proyecto Laravel 10 para reemplazar la solución previa en Google Apps Script. Gestiona programas de fidelización para múltiples comercios mediante una arquitectura **database-per-tenant** (MySQL principal + SQLite por comercio).

---

## 📌 Estado Actual (30/09/2025)

- Webhook único `POST /api/webhook/ingest` con Adapter Pattern y tenant por RUT.
- Panel SuperAdmin (`/superadmin`) para configuración global, gestión de tenants y bandeja de webhooks.
- Panel por comercio (`/{tenant}`) con autenticación y roles (Admin, Supervisor, Operario).
- Módulos implementados: dashboard, clientes, canje de puntos, promociones, reportes CSV, usuarios, configuración del tenant, portal público de autoconsulta.
- Documentación consolidada en `docs/` (ver sección [Documentación](#documentación)).

---

## 🔑 Accesos

| Tipo | URL | Credenciales iniciales |
|------|-----|------------------------|
| **SuperAdmin** | `/superadmin/login` | `superadmin@puntos.local / superadmin123` (creado por seeder) |
| **Comercio (tenant demo)** | `/000000000016/login` | `admin@demo.local / admin123` (desde `TenantUserSeeder`) |

> El SuperAdmin administra tenants y configuraciones globales. Cada tenant tiene su propia base SQLite y usuarios aislados.

---

## 🏗️ Arquitectura Resumida

- **Backend:** Laravel 10, PHP 8.1+
- **Frontend:** Blade + Bootstrap 5 + Vanilla JS
- **Base principal (MySQL):** `tenants`, `system_config`, `webhook_inbox_global`, `admin_logs`, `users`
- **Base por tenant (SQLite):** `clientes`, `facturas`, `puntos_canjeados`, `puntos_vencidos`, `promociones`, `usuarios`, `actividades`, `webhook_inbox`, `whatsapp_logs`
- **Notificaciones:** configuración centralizada (SMTP + WhatsApp) gestionada por SuperAdmin.
- **Procesos clave:** webhook → adapter → `PuntosService` → registros en SQLite + logs globales.

Detalles ampliados en `docs/ARQUITECTURA.md`.

---

## 🚀 Puesta en Marcha (local)

```bash
cd app
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

- Bases SQLite se guardan en `storage/tenants/{rut}.sqlite`.
- Seeders generan tenant demo y usuarios de prueba.
- Emulador de webhook: `php scripts/emulador_webhook.php`.

---

## ✅ Testing Manual

1. Iniciar `php artisan serve`.
2. Login SuperAdmin (`/superadmin/login`) → validar dashboard, configuración, creación de tenant.
3. Login tenant (`/000000000016/login`) → recorrer módulos (clientes, canjes, promociones, reportes, configuración).
4. Probar portal público: `/000000000016/consulta` con documento de cliente demo (`14382361`).
5. Usar emulador para verificar ingreso de facturas y aplicación de promociones.

Guías detalladas: `MANUAL_USUARIO.md` y `docs/CHECKLIST_TAREAS.md`.

---

## 📁 Estructura Relevante

```
app/
├── app/
│   ├── Http/Controllers/     # Webhook, paneles, módulos
│   ├── Http/Middleware/      # multitenant + superadmin guard
│   ├── Models/               # MySQL + SQLite
│   └── Services/PuntosService.php
├── database/
│   ├── migrations/           # MySQL + actualizaciones tenants
│   └── seeders/              # SuperAdmin + tenant demo
├── resources/views/
│   ├── layouts/app.blade.php # Layout tenant
│   └── superadmin/           # Panel SuperAdmin
└── scripts/emulador_webhook.php
```

---

## 📚 Documentación

| Documento | Descripción |
|-----------|-------------|
| `docs/ARQUITECTURA.md` | Diseño técnico actualizado, flujos y tablas |
| `MANUAL_USUARIO.md` | Uso del sistema (SuperAdmin + Tenant) |
| `MANUAL_DEPLOYMENT.md` | Guía de instalación y mantenimiento |
| `docs/CHECKLIST_TAREAS.md` | Lista de pruebas y tareas operativas |
| `CHANGELOG.md` | Historial de cambios del proyecto |

Documentación previa y material de referencia se mantiene en `docs/archive/`.

---

## 🤝 Contribución

1. Crear feature branch.
2. Ejecutar `php artisan test` (por implementar tests unitarios/feature).
3. Actualizar `CHANGELOG.md` y documentación si corresponde.
4. Crear PR con descripción y pasos de prueba.

---

**© 2025 Sistema de Puntos** — Desarrollo incremental, sin dependencias externas adicionales.