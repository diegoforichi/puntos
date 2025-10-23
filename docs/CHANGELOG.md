### 2025-10-23
- Añadí `allow_custom_whatsapp` y `allow_custom_email` en `tenants` para habilitar credenciales propias por canal.
- Creé migración tenant para tablas `campanas` y `campana_envios` (base para envíos masivos).
- Implementé `NotificationConfigResolver` y actualicé `WhatsAppService` y envíos de reportes diarios para usar config del tenant o global según corresponda.
- SuperAdmin ahora puede activar/desactivar credenciales personalizadas y el tenant tiene pestaña `Integraciones` para gestionar WhatsApp/API y email SMTP.

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

