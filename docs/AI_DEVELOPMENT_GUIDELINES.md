# Guía de Desarrollo Asistido por IA
## Mitigando Riesgos y Maximizando Beneficios

**Versión**: 1.0  
**Última actualización**: 2025-10-16  
**Aplica a**: Desarrollo con asistentes de IA (Claude, ChatGPT, Copilot, Cursor, etc.)

---

## 🎯 PROPÓSITO

Los asistentes de IA son herramientas poderosas, pero tienen limitaciones y riesgos. Este documento establece reglas y prácticas para:

1. **Minimizar** errores y alucinaciones
2. **Maximizar** productividad y calidad
3. **Mantener** consistencia en el código
4. **Garantizar** seguridad y mantenibilidad

---

## 🚨 PROBLEMAS COMUNES CON IA

### 1. Alucinaciones
**Problema**: IA inventa código, funciones o paquetes que no existen.

**Ejemplo**:
```php
// ❌ IA puede sugerir esto (NO EXISTE)
$invoice->autoCalculateTotal();  // Método que no existe en Laravel
use App\Helpers\MagicCalculator;  // Clase que no existe
```

**Solución**:
- ✅ Verificar contra documentación oficial
- ✅ Tests automáticos detectan errores
- ✅ Code review obligatorio

### 2. Sobre-ingeniería
**Problema**: IA sugiere soluciones excesivamente complejas.

**Ejemplo**:
```php
// ❌ IA puede sugerir esto (COMPLEJO)
interface InvoiceRepositoryInterface {}
class InvoiceRepository implements InvoiceRepositoryInterface {}
class InvoiceService {
    private $repository;
    // Muchas capas innecesarias para CRUD simple
}

// ✅ MEJOR (SIMPLE)
class InvoiceController extends Controller {
    public function index() {
        return view('invoices.index', [
            'invoices' => Invoice::with('client')->paginate(20)
        ]);
    }
}
```

**Solución**:
- ✅ Regla explícita: "buscar simplicidad"
- ✅ Pedir alternativas simples
- ✅ Rechazar código innecesariamente complejo

### 3. Código Desactualizado
**Problema**: IA usa sintaxis o paquetes de versiones antiguas.

**Ejemplo**:
```php
// ❌ Laravel 8 (desactualizado)
protected $casts = [
    'date' => 'datetime',
];

// ✅ Laravel 10 (actual)
protected function casts(): array
{
    return [
        'date' => 'datetime',
    ];
}
```

**Solución**:
- ✅ Especificar versiones en prompts
- ✅ Context7 con docs actualizadas
- ✅ Verificar contra changelog

### 4. Hardcode de Textos
**Problema**: IA pone textos directamente en vistas.

**Ejemplo**:
```blade
{{-- ❌ IA puede generar esto (HARDCODE) --}}
<h1>Productos</h1>
<button>Crear Producto</button>

{{-- ✅ CORRECTO (TRADUCCIONES) --}}
<h1>{{ __('models.product.plural') }}</h1>
<button>{{ __('actions.create') }} {{ __('models.product.singular') }}</button>
```

**Solución**:
- ✅ Sistema de traducciones obligatorio
- ✅ grep para detectar hardcode
- ✅ Reglas explícitas en contexto

### 5. Dependencias Innecesarias
**Problema**: IA agrega paquetes que no se necesitan.

**Ejemplo**:
```json
// ❌ IA puede sugerir esto
{
    "require": {
        "guzzlehttp/guzzle": "^7.0",  // Laravel ya tiene HTTP client
        "nesbot/carbon": "^2.0",      // Laravel ya lo incluye
        "doctrine/dbal": "^3.0"       // Solo si modificas columnas
    }
}
```

**Solución**:
- ✅ Revisar composer.json después de cambios
- ✅ Justificar cada dependencia
- ✅ Preferir Laravel nativo

### 6. Falta de Tests
**Problema**: IA genera código sin tests.

**Solución**:
- ✅ Siempre pedir tests en el prompt
- ✅ CI/CD requiere tests
- ✅ Cobertura mínima del 70%

### 7. Ignorar Limitaciones del Servidor
**Problema**: IA sugiere soluciones incompatibles con hosting compartido.

**Ejemplo**:
```bash
# ❌ IA puede sugerir esto (NO FUNCIONA en shared hosting)
npm install
composer install
php artisan queue:work --daemon
```

**Solución**:
- ✅ Especificar hosting compartido en prompt
- ✅ Contexto persistente con limitaciones
- ✅ Rechazar soluciones incompatibles

---

## ✅ ESTRATEGIAS DE MITIGACIÓN

### 1. Contexto Persistente Obligatorio

**Archivos que el asistente DEBE leer**:
```
proyecto/
├── docs/
│   ├── GENERAL_RULES.md              # Reglas universales
│   ├── AI_DEVELOPMENT_GUIDELINES.md  # Este documento
│   └── CONTEXT.md                    # Resumen del proyecto
├── .cursor/rules/
│   ├── project-rules.md              # Reglas específicas
│   ├── i18n-rules.md                 # Sistema de traducciones
│   ├── code-conventions.md           # Convenciones
│   └── deployment-rules.md           # Despliegue
└── README.md                         # Instalación
```

**Actualizar contexto cuando**:
- Cambies servidor/hosting
- Agregues/quites stack tecnológico
- Modifiques arquitectura
- Descubras nueva limitación
- Mensualmente (mínimo)

### 2. Prompts Efectivos

**❌ Prompt Malo**:
```
"Crea un CRUD de facturas"
```

**✅ Prompt Bueno**:
```
"Crea un CRUD de facturas siguiendo @GENERAL_RULES.md.

Contexto:
- Laravel 10, PHP 8.2, MySQL 5.7
- Hosting compartido (sin Node.js en servidor)
- Stack: Blade + Tailwind + Livewire

Requisitos:
1. Usar sistema de traducciones (NO hardcode)
2. Incluir tests (Feature + Unit)
3. Documentar con PHPDoc
4. Mantener SIMPLE
5. Eager loading para relaciones
6. Validación con Form Request

Entregar:
- Migración
- Modelo con relaciones
- Controlador
- Vistas con traducciones
- Form Request
- Tests
"
```

### 3. Checklist de Verificación

**Antes de aceptar código de IA**:

#### Cumplimiento de Reglas:
- [ ] ¿Sigue GENERAL_RULES.md?
- [ ] ¿Usa sistema de traducciones?
- [ ] ¿Es simple y mantenible?
- [ ] ¿Compatible con hosting compartido?

#### Calidad de Código:
- [ ] ¿Tiene documentación (PHPDoc)?
- [ ] ¿Usa Laravel nativo?
- [ ] ¿No agrega dependencias innecesarias?
- [ ] ¿Sigue convenciones de nombres?

#### Tests y Seguridad:
- [ ] ¿Incluye tests?
- [ ] ¿Valida inputs?
- [ ] ¿No tiene vulnerabilidades obvias?
- [ ] ¿Logs de eventos importantes?

#### Performance:
- [ ] ¿Usa eager loading?
- [ ] ¿Implementa paginación?
- [ ] ¿No tiene N+1 queries?
- [ ] ¿Cache donde corresponde?

### 4. Red Flags (Rechazar Inmediatamente)

Si el código de IA contiene esto, **rechazar**:

- ❌ Paquetes no listados en `composer.json`
- ❌ Código que requiere Node.js en servidor
- ❌ Textos hardcodeados en vistas
- ❌ SQL manual en lugar de Eloquent
- ❌ JavaScript complejo sin justificación
- ❌ Docker/Sail para producción
- ❌ Modificación de archivos de vendor/
- ❌ Código sin documentación
- ❌ Funciones que no existen en Laravel
- ❌ Sintaxis de versiones antiguas

### 5. Proceso de Code Review Post-IA

**Flujo obligatorio**:

```
1. IA genera código
   ↓
2. Revisar contra GENERAL_RULES.md
   ↓
3. Verificar tests incluidos
   ↓
4. Ejecutar Laravel Pint
   php artisan pint
   ↓
5. Ejecutar tests
   php artisan test
   ↓
6. Probar localmente
   ↓
7. Verificar no hay hardcode
   grep -r "hardcoded_text" resources/views/
   ↓
8. Code review manual
   ↓
9. Solo entonces: commit
```

### 6. Detección de Hardcode Automatizada

**Script de verificación**:

```bash
#!/bin/bash
# check-hardcode.sh

echo "🔍 Buscando textos hardcodeados..."

# Buscar textos comunes hardcodeados en español
PATTERNS=(
    "Productos"
    "Clientes"
    "Facturas"
    "Crear"
    "Editar"
    "Eliminar"
    "Guardar"
)

FOUND=0

for pattern in "${PATTERNS[@]}"; do
    if grep -r --include="*.blade.php" -F "$pattern" resources/views/ 2>/dev/null; then
        echo "❌ Encontrado hardcode: $pattern"
        FOUND=1
    fi
done

if [ $FOUND -eq 0 ]; then
    echo "✅ No se encontró hardcode"
    exit 0
else
    echo ""
    echo "❌ Se encontró hardcode. Usa el sistema de traducciones:"
    echo "   {{ __('models.product.plural') }}"
    exit 1
fi
```

---

## 🎯 CONFIGURACIÓN DEL ASISTENTE

### En Cursor

**Archivos de reglas** (`.cursor/rules/`):
```markdown
## COMPORTAMIENTO DEL ASISTENTE

### SIEMPRE:
- ✅ Leer GENERAL_RULES.md antes de responder
- ✅ Usar sistema de traducciones (NO hardcode)
- ✅ Incluir tests para nuevo código
- ✅ Documentar con PHPDoc
- ✅ Buscar simplicidad
- ✅ Considerar hosting compartido
- ✅ Ofrecer múltiples alternativas
- ✅ Justificar decisiones

### NUNCA:
- ❌ Hardcodear textos en vistas
- ❌ Sugerir Docker/Sail para producción
- ❌ Agregar dependencias sin justificar
- ❌ Usar JavaScript complejo sin necesidad
- ❌ Ignorar limitaciones del servidor
- ❌ Generar código sin tests
- ❌ Omitir documentación
```

### En Claude / ChatGPT

**Prompt de Sistema**:
```
Eres un experto en Laravel trabajando en hosting compartido.

REGLAS OBLIGATORIAS:
1. Leer docs/GENERAL_RULES.md antes de sugerir código
2. NUNCA hardcodear textos, usar __('models.xxx')
3. Mantener SIMPLICIDAD, no sobre-ingenierizar
4. Compatible con shared hosting (sin Node.js, Docker)
5. Incluir tests para código nuevo
6. Documentar con PHPDoc
7. Justificar decisiones técnicas

STACK:
- Laravel 10, PHP 8.2, MySQL 5.7
- Blade + Tailwind + Livewire
- Sin Node.js en servidor
- Subir vendor/ completo

LIMITACIONES:
- Hosting compartido
- No root access
- No procesos background
- Memoria limitada
- Sin composer/npm en servidor
```

---

## 📊 MÉTRICAS DE CALIDAD

### Indicadores de Código de IA Aceptable:

✅ **Buenas señales**:
- Código simple y directo
- Usa Laravel nativo
- Incluye PHPDoc
- Tiene tests
- Usa traducciones
- Sigue convenciones
- No dependencias nuevas sin justificar

❌ **Malas señales**:
- Múltiples capas de abstracción
- Paquetes desconocidos
- Sin documentación
- Sin tests
- Textos hardcodeados
- Sintaxis desactualizada
- Ignora limitaciones del servidor

---

## 🔄 ITERACIÓN CON IA

### Mejora Iterativa:

**Primera iteración**:
```
"Crea un CRUD de facturas siguiendo @GENERAL_RULES.md"
```

**Si el resultado no es óptimo**:
```
"El código está bien, pero:
1. Simplifica InvoiceController, no necesitamos repositorio
2. Agrega traducciones en lugar de textos hardcodeados
3. Incluye tests para create y update
4. Documenta el método store() con PHPDoc

Mantén: Laravel 10, hosting compartido, Blade + Tailwind"
```

**Refinamiento**:
```
"Perfecto, ahora:
1. Agrega eager loading para relación con cliente
2. Implementa paginación en index
3. Valida con Form Request en lugar de validate()

No cambies el resto del código, solo estas mejoras"
```

### Feedback Constructivo:

En lugar de:
```
❌ "Esto está mal, hazlo de nuevo"
```

Usa:
```
✅ "El código funciona, pero podemos simplificarlo.
    En lugar de usar Repository Pattern para un CRUD simple,
    usa Eloquent directamente en el controlador.
    Mantén el resto igual."
```

---

## 📚 RECURSOS PARA IA

### Documentación a Proporcionar:

```
Cuando pidas ayuda a IA, incluye:

@docs/GENERAL_RULES.md           # Reglas universales
@.cursor/rules/project-rules.md  # Reglas específicas
@docs/CONTEXT.md                 # Contexto del proyecto
@composer.json                   # Dependencias actuales
@routes/web.php                  # Rutas existentes
```

### Ejemplos de Código de Referencia:

Mantén ejemplos de **código correcto** para que la IA los use como referencia:

```
examples/
├── controller-example.php    # Controlador bien hecho
├── model-example.php         # Modelo con relaciones
├── view-example.blade.php    # Vista con traducciones
├── test-example.php          # Test completo
└── migration-example.php     # Migración estándar
```

---

## 🎯 CASOS DE USO EXITOSOS

### 1. Generación de CRUD:

**Prompt**:
```
"Genera CRUD completo para modelo Product siguiendo @GENERAL_RULES.md.

Incluir:
- Migración (name, description, price, stock)
- Modelo con $fillable y casts
- Controlador con todos los métodos
- Form Request para validación
- Vistas index, create, edit (con traducciones)
- Tests Feature

Relaciones:
- belongsToMany Category

Laravel 10, Blade + Tailwind, hosting compartido"
```

### 2. Refactorización:

**Prompt**:
```
"Refactoriza este controlador siguiendo KISS principle.

Archivo: @app/Http/Controllers/InvoiceController.php

Objetivos:
1. Simplificar métodos largos
2. Extraer lógica compleja a métodos privados
3. Agregar eager loading
4. Documentar con PHPDoc
5. NO cambiar funcionalidad

Mantener compatibilidad con hosting compartido"
```

### 3. Optimización:

**Prompt**:
```
"Optimiza estas queries para performance.

Problema: N+1 queries en invoice index

Archivo: @app/Http/Controllers/InvoiceController.php

Solución esperada:
1. Eager loading de relaciones
2. Paginación si no existe
3. Select específico si es posible
4. Mantener código simple

No agregar dependencias, usar Eloquent nativo"
```

---

## 🔒 SEGURIDAD CON IA

### Datos Sensibles:

**NUNCA compartir con IA**:
- ❌ Contraseñas reales
- ❌ API keys de producción
- ❌ Datos de clientes reales
- ❌ Información financiera real

**SÍ compartir**:
- ✅ Código (sin credenciales)
- ✅ Estructura de datos
- ✅ Ejemplos ficticios
- ✅ Configuración (sin secrets)

### Sanitización:

**Antes de pedir ayuda**:
```php
// ❌ NO compartir
'api_key' => 'sk_live_123456789'

// ✅ Sanitizado
'api_key' => 'sk_live_XXXXX'
```

---

## 📋 CHECKLIST FINAL

### Antes de Aceptar Código de IA:

**Funcionalidad**:
- [ ] El código hace lo que se pidió
- [ ] No rompe funcionalidad existente
- [ ] Funciona localmente

**Reglas**:
- [ ] Sigue GENERAL_RULES.md
- [ ] Compatible con hosting compartido
- [ ] Usa sistema de traducciones
- [ ] Es simple y mantenible

**Calidad**:
- [ ] Tiene documentación (PHPDoc)
- [ ] Incluye tests
- [ ] Sigue convenciones de nombres
- [ ] No hay código duplicado

**Seguridad**:
- [ ] Valida inputs
- [ ] No tiene vulnerabilidades obvias
- [ ] Usa Eloquent (no SQL manual)
- [ ] Logs de eventos importantes

**Performance**:
- [ ] Usa eager loading
- [ ] Tiene paginación
- [ ] No hay N+1 queries
- [ ] Cache donde corresponde

**Deploy**:
- [ ] No requiere instalaciones en servidor
- [ ] Funciona sin Node.js/Composer en producción
- [ ] Assets compilados localmente
- [ ] Compatible con permisos limitados

---

## 🔄 MANTENIMIENTO DE ESTE DOCUMENTO

### Actualizar cuando:
- Descubras nuevos patrones de error en IA
- Encuentres nuevas estrategias de mitigación
- Cambien capacidades de asistentes IA
- Se identifiquen nuevos riesgos

### Feedback:
Si encuentras un problema recurrente con IA, documéntalo aquí para referencia futura.

---

**Nota**: Este documento es complementario a `GENERAL_RULES.md`. Ambos deben usarse en conjunto para desarrollo con IA.

**Ver también**:
- `GENERAL_RULES.md` - Reglas universales de desarrollo
- `SECURITY_CHECKLIST.md` - Checklist de seguridad
- `.cursor/rules/` - Reglas técnicas específicas

