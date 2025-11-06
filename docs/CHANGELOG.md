### 2025-11-04
- **Correcciones críticas:**
  - Corregido error de tipo en `ConfiguracionController::probarWhatsAppPersonalizado()`: ahora pasa correctamente `$config` como primer parámetro a `WhatsAppService::enviar()`.
  - Removido `@include('partials.alerts')` innecesario de `clientes/create.blade.php` (las alertas ya están en el layout principal).
- **Gestión avanzada de campañas:**
  - Agregada migración `2025_11_04_000000_add_soft_deletes_and_paused_to_campanas.php` para soft deletes y índice en `estado`.
  - Modelo `Campana` ahora usa `SoftDeletes` y tiene métodos helper: `puedeEditarse()`, `puedeEliminarse()`, `puedePausarse()`, `puedeReanudarse()`, `puedeEnviarse()`.
  - Nuevos métodos en `CampanaController`: `pause()`, `resume()`, `destroy()` con validaciones de estado y registro de actividad.
  - Rutas agregadas: `POST /campanas/{id}/pausar`, `POST /campanas/{id}/reanudar`, `DELETE /campanas/{id}`.
  - Vista `campanas/index.blade.php`: dropdown con acciones contextuales según estado (pausar, reanudar, enviar, eliminar).
  - Vista `campanas/show.blade.php`: botones dinámicos de acción según estado, con mensajes informativos si no hay acciones disponibles.
  - Badges de estado mejorados con colores: `pausada` (gris), `en_cola`/`enviando` (azul), `fallida` (rojo), `completada` (verde), `pendiente` (amarillo).
- **Formulario de creación de clientes:**
  - Formulario manual de creación de clientes en `clientes/create.blade.php` (sin dependencia de facturación previa).
  - Métodos `create()` y `store()` en `ClienteController` para registro manual con puntos iniciales opcionales.
- **Personalización de mensajes de campaña:**
  - Placeholders extendidos en WhatsApp y Email: `{nombre}`, `{puntos}`, `{comercio}`, `{telefono}`, `{email}`, `{documento}`.
  - Límite de 200 caracteres para mensajes de WhatsApp con contador en tiempo real.
  - `ProcesarEnvioCampana` valida presencia de teléfono/email antes de intentar enviar.

### 2025-10-25
- Base del módulo de campañas: modelos `Campana`/`CampanaEnvio`, vistas (index/create/show) y rutas tenant.
- Comando `campanas:procesar-programadas` que encola campañas con fecha vencida.
- Jobs `EnviarCampanaJob` y `ProcesarEnvioCampana` para envíos asíncronos con rate limiting.
- Plantilla de email `CampanaMail` + `emails/campana.blade.php` con placeholders `{nombre}`/`{puntos}`.
- Ajustes en `NotificationConfigResolver` y `WhatsAppService` para reutilizar credenciales resueltas.
- Migraciones tenant actualizadas: columna `tenant_id` en `campanas`, `canal` en `campana_envios` y paths corregidos en comandos `tenant:migrate` y seed de superadmin.
- `CampanaController` guarda totales como JSON nativo, programa fechas en formato `Y-m-d H:i:s` para SQLite y encola envíos inmediatos.
- `ProcesarEnvioCampana` valida datos de contacto, aplica configuración SMTP por tenant antes de enviar y registra fallos detallados.
- Sidebar actualizado para linkear sección `Campañas`.

# Changelog

Historial de cambios del proyecto.

Formato basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/),  
y este proyecto sigue [Semantic Versioning](https://semver.org/lang/es/).

---

## [Sin versionar] - 2025-10-16

### Agregado
- Documentación completa del proyecto
- `docs/GENERAL_RULES.md` - Reglas universales para todos los proyectos
- `docs/AI_DEVELOPMENT_GUIDELINES.md` - Guía para desarrollo con IA
- `docs/SECURITY_CHECKLIST.md` - Checklist de seguridad
- `docs/CONTEXT.md` - Contexto del proyecto
- `docs/INDEX.md` - Índice de documentación
- `docs/QUICK_START.md` - Inicio rápido
- Sistema de traducciones en `resources/lang/es/`
  - `models.php` - Nombres de modelos
  - `navigation.php` - Menús y navegación
  - `actions.php` - Acciones CRUD
  - `messages.php` - Mensajes generales
  - `attributes.php` - Atributos/campos
- Reglas de Cursor en `.cursor/rules/`
  - `project-rules.md` - Reglas del proyecto
  - `i18n-rules.md` - Reglas de internacionalización
  - `code-conventions.md` - Convenciones de código
  - `deployment-rules.md` - Reglas de deployment
  - `technical-context.md` - Contexto técnico

### Configurado
- Idioma por defecto: Español (es)
- Laravel 12.34.0
- Tailwind CSS v4
- Optimizado para hosting compartido

---

## Formato

Los tipos de cambios son:
- `Agregado` - Nueva funcionalidad
- `Cambiado` - Cambios en funcionalidad existente
- `Deprecado` - Funcionalidad que se eliminará pronto
- `Eliminado` - Funcionalidad eliminada
- `Corregido` - Corrección de bugs
- `Seguridad` - Vulnerabilidades corregidas

---

**Nota**: Las versiones se agregarán cuando se haga el primer release del proyecto.

