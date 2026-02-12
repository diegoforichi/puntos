# Documentación - Sistema de Puntos (Multi-Tenant)

Documentación del proyecto **Sistema de Puntos** (Laravel) optimizado para hosting compartido.

---

## 🎯 Información del Proyecto

- **Framework**: Laravel 10.x
- **PHP**: 8.2.12
- **Base de Datos**: MySQL (global) + SQLite (por tenant)
- **Frontend**: Blade + Tailwind CSS v4
- **Hosting**: Compartido (Apache 2.4.59)

---

## 📚 Documentación

### Documentos Universales (para TODOS los proyectos):

- **[docs/GENERAL_RULES.md](docs/GENERAL_RULES.md)** ⭐
  - Reglas universales de desarrollo
  - Stack tecnológico permitido
  - Filosofía y principios
  - Proceso de deployment
  - **Usar como base para cualquier proyecto Laravel**

- **[docs/AI_DEVELOPMENT_GUIDELINES.md](docs/AI_DEVELOPMENT_GUIDELINES.md)** ⭐
  - Trabajar con asistentes de IA
  - Mitigar alucinaciones y errores
  - Prompts efectivos
  - Checklist de verificación de código

- **[docs/SECURITY_CHECKLIST.md](docs/SECURITY_CHECKLIST.md)** ⭐
  - Checklist completo de seguridad
  - Antes, durante y después del deploy
  - Protecciones de Laravel
  - Plan de respuesta a incidentes

- **[docs/CONTEXT.md](docs/CONTEXT.md)**
  - Resumen rápido del proyecto actual
  - Flujo de trabajo
  - Recordatorios importantes

### Reglas Técnicas (específicas de este proyecto):

- **[.cursor/rules/project-rules.md](.cursor/rules/project-rules.md)**
  - Filosofía del proyecto
  - Limitaciones del servidor
  - Stack tecnológico específico

- **[.cursor/rules/i18n-rules.md](.cursor/rules/i18n-rules.md)**
  - Sistema de traducciones obligatorio
  - NUNCA hardcodear textos
  - Cambios centralizados

- **[.cursor/rules/code-conventions.md](.cursor/rules/code-conventions.md)**
  - Convenciones de código
  - Ejemplos de PHPDoc
  - Buenas prácticas

- **[.cursor/rules/deployment-rules.md](.cursor/rules/deployment-rules.md)**
  - Proceso de deployment
  - Qué subir al servidor
  - Configuración de producción

---

## 🚀 Instalación

### Desarrollo Local:

```bash
# 1. Clonar proyecto
git clone [url-del-repo]
cd proyecto

# 2. Instalar dependencias PHP
composer install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos
# Editar .env con tus credenciales MySQL
# O crear database.sqlite en database/

# 5. Ejecutar migraciones
php artisan migrate

# 6. (Opcional) Seeders para datos de prueba
php artisan db:seed

# 7. Instalar dependencias frontend
npm install

# 8. Iniciar desarrollo
composer run dev
# O manualmente:
php artisan serve
npm run dev
```

### Producción:

Ver **[docs/GENERAL_RULES.md#deployment](docs/GENERAL_RULES.md)** para proceso completo.

**Resumen**:
1. Tests pasando
2. Compilar assets localmente: `npm run build`
3. Optimizar: `composer install --optimize-autoloader --no-dev`
4. Subir vendor/ completo (NO ejecutar composer en servidor)
5. Configurar .env de producción
6. Ejecutar migraciones
7. Optimizar Laravel (config/route/view cache)

---

## 🧪 Testing

```bash
# Ejecutar todos los tests
php artisan test

# Con cobertura
php artisan test --coverage

# Solo un test específico
php artisan test --filter test_can_create_invoice

# Verificar estilo de código
vendor/bin/pint --test
```

---

## 🌍 Internacionalización

Este proyecto usa sistema de traducciones obligatorio:

```blade
{{-- ❌ NO hacer --}}
<h1>Productos</h1>

{{-- ✅ HACER --}}
<h1>{{ __('models.product.plural') }}</h1>
```

**Archivos de idioma**: `resources/lang/es/`

**Beneficio**: Cambiar textos = editar 1 archivo, todo se actualiza.

Ver **[.cursor/rules/i18n-rules.md](.cursor/rules/i18n-rules.md)** para detalles.

---

## 📦 Comandos Útiles

```bash
# Desarrollo
composer run dev          # Servidor + Queue + Logs + Vite
php artisan serve        # Solo servidor
npm run dev              # Solo Vite

# Base de datos
php artisan migrate      # Ejecutar migraciones
php artisan migrate:fresh --seed  # Reset + seeds
php artisan db:seed      # Solo seeders

# Testing
php artisan test         # Tests
vendor/bin/pint          # Formatear código
php artisan test --coverage  # Cobertura

# Producción (en servidor)
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 🔧 Stack Tecnológico

### Backend:
- Laravel 10.x
- PHP 8.2.12
- MySQL 5.7.23
- Apache 2.4.59

### Frontend:
- Tailwind CSS v4
- Blade Templates
- Vite 7.0.7
- Alpine.js (opcional)

### Herramientas:
- Laravel Boost (MCP)
- Laravel Pint (formateo)
- PHPUnit (testing)
- Composer (dependencias)

---

## 🚨 Limitaciones del Hosting

Este proyecto está optimizado para **hosting compartido**:

- ❌ NO Node.js en servidor
- ❌ NO Docker/Sail en producción
- ❌ NO ejecutar composer/npm en servidor
- ✅ SÍ subir vendor/ completo
- ✅ SÍ compilar assets localmente
- ✅ SÍ MySQL global + SQLite por tenant (según diseño del proyecto)

Ver **[docs/GENERAL_RULES.md](docs/GENERAL_RULES.md)** para limitaciones completas.

---

## 📞 Soporte

### Desarrollo:
- Email: [tu email]
- GitHub Issues: [link]

### Hosting:
- cPanel: [url]
- Soporte: [contacto hosting]

---

## 📄 Licencia

Este proyecto es privado y propietario. Todos los derechos reservados.

---

## 🔄 Changelog

Ver [CHANGELOG.md](CHANGELOG.md) para historial de cambios.

---

## 🎯 Próximos Pasos

1. **Leer**: `docs/GENERAL_RULES.md` (reglas universales)
2. **Revisar**: `docs/AI_DEVELOPMENT_GUIDELINES.md` (si usas IA)
3. **Verificar**: `docs/SECURITY_CHECKLIST.md` (antes de deploy)
4. **Empezar**: Desarrollo siguiendo las reglas

---

**Nota**: Este es un proyecto plantilla que sigue las mejores prácticas para desarrollo Laravel en hosting compartido. Puedes usarlo como base para cualquier aplicación.

**Última actualización**: 2026-02-11
