# Reglas del Proyecto - Panel de Facturas Laravel

## 🎯 FILOSOFÍA DEL PROYECTO

### Principios Fundamentales:
1. **SIMPLICIDAD PRIMERO**: Priorizar soluciones simples y directas
2. **MANTENIBILIDAD**: Código fácil de entender y modificar
3. **EFECTIVIDAD**: Soluciones que funcionen sin complejidad innecesaria
4. **VELOCIDAD**: Rendimiento óptimo sin sacrificar claridad
5. **AUTONOMÍA**: No depender de instalaciones externas en servidor

### Enfoque de Desarrollo:
- **Ofrecer alternativas**: Siempre sugerir múltiples opciones con pros/contras
- **Explicar decisiones**: Justificar por qué una solución es mejor
- **Buscar lo simple**: Si hay una forma más simple, elegirla
- **Evitar sobre-ingeniería**: No usar patrones complejos si no son necesarios
- **Auto-contenido**: Todo debe estar incluido en el proyecto (vendor, node_modules compilados)

---

## 🚨 LIMITACIONES CRÍTICAS DEL SERVIDOR

### Hosting Compartido:
- **Servidor**: Apache 2.4.59
- **PHP**: 8.2.12 (versión específica)
- **MySQL**: 5.7.23 (versión antigua)
- **SQLite**: VERSIÓN ANTIGUA - NO usar en producción
- **Recursos**: Limitados (memoria, CPU, tiempo de ejecución)

### Restricciones:
- ❌ NO Node.js en producción
- ❌ NO Docker/Sail en producción
- ❌ NO procesos intensivos
- ❌ NO dependencias externas que requieran instalación
- ❌ NO compilación en servidor
- ✅ SÍ PHP y MySQL
- ✅ SÍ Laravel nativo
- ✅ SÍ soluciones simples
- ✅ SÍ subir directorios completos (vendor, assets compilados)

---

## 🛠️ STACK TECNOLÓGICO PERMITIDO

### Backend (OBLIGATORIO):
- **Framework**: Laravel 12.34.0
- **PHP**: 8.2.12
- **Base de Datos**: MySQL 5.7.23 (producción), SQLite (solo desarrollo local)
- **ORM**: Eloquent (nativo de Laravel)

### Frontend (OBLIGATORIO):
- **CSS**: Tailwind CSS v4 (ya configurado)
- **Templates**: Blade (nativo de Laravel)
- **JavaScript**: MÍNIMO necesario, preferir Alpine.js si es esencial
- **Admin Panel**: Filament (compatible con hosting compartido)

### JavaScript (RESTRICCIONES IMPORTANTES):
- ❌ NO Vanilla JavaScript complejo
- ❌ NO frameworks pesados (React, Vue en producción)
- ❌ NO Node.js en producción
- ❌ NO compilación de assets en servidor
- ✅ SÍ Alpine.js (ligero, integrado con Livewire/Filament)
- ✅ SÍ JavaScript mínimo inline en Blade
- ✅ SÍ Livewire para interactividad (sin JavaScript)

---

## 📦 DEPENDENCIAS Y DESPLIEGUE

### Estrategia de Dependencias:
- **TODO incluido**: Subir vendor/ completo al servidor
- **Assets compilados**: Compilar localmente, subir build/
- **NO instalaciones**: No ejecutar composer install en servidor
- **NO compilación**: No ejecutar npm build en servidor
- **Auto-contenido**: El proyecto debe funcionar sin instalaciones adicionales

### Archivos a subir (COMPLETOS):
- ✅ vendor/ (completo, con todas las dependencias)
- ✅ public/ (con assets compilados)
- ✅ app/ (código de la aplicación)
- ✅ bootstrap/ (archivos de bootstrap)
- ✅ config/ (configuración)
- ✅ database/ (migraciones, seeders)
- ✅ resources/ (vistas, assets sin compilar)
- ✅ routes/ (rutas)
- ✅ storage/ (con permisos correctos)
- ✅ .env (configuración de producción)
- ✅ artisan (comando CLI)
- ✅ composer.json y composer.lock

### Archivos a NO subir:
- ❌ node_modules/ (solo assets compilados)
- ❌ .git/ (control de versiones)
- ❌ .env.local (configuración local)
- ❌ tests/ (opcional, solo si no se usan en prod)

---

## 💡 ENFOQUE DE SOLUCIONES

### Al sugerir código:
1. **Ofrecer múltiples alternativas**:
   ```
   Opción 1 (Simple): Usar Eloquent directo
   - Pros: Fácil de entender, menos código, auto-contenido
   - Contras: Menos flexible
   
   Opción 2 (Intermedia): Usar Repository Pattern
   - Pros: Más organizado, testeable
   - Contras: Más archivos, más complejo
   
   Opción 3 (Avanzada): Usar CQRS
   - Pros: Muy escalable
   - Contras: Sobrecarga para este proyecto
   
   Recomendación: Opción 1 (suficiente para tus necesidades)
   ```

2. **Justificar decisiones**:
   - Explicar POR QUÉ una solución es mejor
   - Considerar el contexto del hosting compartido
   - Priorizar simplicidad y mantenibilidad
   - Verificar que no requiera dependencias externas

3. **Buscar lo más simple**:
   - Si Laravel tiene una solución nativa, usarla
   - No inventar soluciones complejas
   - No agregar dependencias innecesarias
   - Todo debe estar auto-contenido

4. **Evitar sobre-ingeniería**:
   - No usar patrones complejos sin justificación
   - No optimizar prematuramente
   - No agregar abstracciones innecesarias
   - No depender de instalaciones externas

---

## 🎯 RESUMEN DE PRIORIDADES

1. **Simplicidad** > Complejidad
2. **Mantenibilidad** > Elegancia
3. **Efectividad** > Perfección
4. **Velocidad** > Características
5. **Laravel nativo** > Paquetes externos
6. **Blade/Livewire** > JavaScript
7. **Eloquent** > SQL manual
8. **MySQL** > SQLite (en producción)
9. **Auto-contenido** > Dependencias externas
10. **Subir completo** > Instalar en servidor

---

## 🌍 INTERNACIONALIZACIÓN (i18n)

### Regla CRÍTICA: NUNCA hardcodear textos
- ❌ NO poner textos directamente en vistas
- ✅ SÍ usar `__('models.xxx')` siempre
- ✅ SÍ crear archivos de idioma para cada modelo
- ✅ SÍ mantener textos centralizados

### Estructura obligatoria:
```
resources/lang/es/
├── models.php        # Nombres de modelos
├── navigation.php    # Menús y navegación
├── actions.php       # Acciones CRUD
└── messages.php      # Mensajes generales
```

### Ejemplo correcto:
```blade
{{-- ✅ BIEN --}}
<h1>{{ __('models.product.plural') }}</h1>
<button>{{ __('actions.create') }} {{ __('models.product.singular') }}</button>

{{-- ❌ MAL --}}
<h1>Productos</h1>
<button>Crear Producto</button>
```

**Ver**: `.cursor/rules/i18n-rules.md` para detalles completos

---

## 🤝 COMPORTAMIENTO DEL ASISTENTE

### SIEMPRE:
- ✅ Ofrecer múltiples soluciones
- ✅ Explicar pros y contras
- ✅ Justificar recomendaciones
- ✅ Priorizar simplicidad
- ✅ Considerar limitaciones del servidor
- ✅ Documentar el código
- ✅ Usar Laravel nativo cuando sea posible
- ✅ Verificar que no requiera instalaciones externas
- ✅ **Usar sistema de traducciones (NUNCA hardcodear textos)**
- ✅ **Crear archivos de idioma para nuevos modelos**

### NUNCA:
- ❌ Sugerir soluciones complejas sin justificación
- ❌ Usar JavaScript complejo sin necesidad
- ❌ Ignorar limitaciones del hosting
- ❌ Agregar dependencias que requieran instalación en servidor
- ❌ Sobre-ingenierizar soluciones simples
- ❌ Omitir documentación en el código
- ❌ Sugerir compilación o instalación en servidor
- ❌ **Hardcodear textos en vistas o controladores**
- ❌ **Crear vistas sin usar sistema de traducciones**

### Formularios
- Evitar placeholders "tontos"; preferir textos neutrales o dejar el campo vacío.

