# Modelos Eloquent - Sistema de Puntos

**Fecha:** 2025-09-29  
**Estado:** ✅ Completado

---

## 📋 Resumen

Se han creado **7 modelos Eloquent** para las tablas del tenant (SQLite), facilitando enormemente el trabajo con la base de datos mediante:
- Relaciones entre modelos
- Scopes personalizados
- Accessors y métodos helper
- Validaciones y lógica de negocio

---

## 🗂️ Modelos Creados

### 1. Cliente (`app/Models/Cliente.php`) - 200 líneas

**Tabla:** `clientes`

#### Propiedades
```php
- documento (string, unique)
- nombre (string)
- telefono (string, nullable)
- email (string, nullable)
- direccion (string, nullable)
- puntos_acumulados (decimal)
- ultima_actividad (datetime)
```

#### Relaciones
- `facturas()`: hasMany → Factura
- `puntosCanjeados()`: hasMany → PuntosCanjeado
- `puntosVencidos()`: hasMany → PuntosVencido
- `facturasActivas()`: hasMany → Factura (no vencidas)
- `facturasPorVencer($dias)`: hasMany → Factura

#### Scopes
- `activos($dias = 30)`: Clientes con actividad reciente
- `conPuntos()`: Clientes con puntos > 0
- `buscar($search)`: Busca por documento, nombre o email

#### Métodos Útiles
- `tienePuntosSuficientes($puntos)`: Valida disponibilidad
- `getTelefonoWhatsappAttribute()`: Formatea con código país (+598)
- `getInicialesAttribute()`: Obtiene iniciales del nombre
- `getPuntosFormateadosAttribute()`: Formatea con separadores

---

### 2. Usuario (`app/Models/Usuario.php`) - 180 líneas

**Tabla:** `usuarios`

#### Propiedades
```php
- nombre (string)
- email (string, unique)
- password (string, hidden)
- rol (enum: admin, supervisor, operario)
- activo (boolean)
- ultimo_acceso (datetime, nullable)
```

#### Constantes
```php
ROL_ADMIN = 'admin'
ROL_SUPERVISOR = 'supervisor'
ROL_OPERARIO = 'operario'
```

#### Relaciones
- `actividades()`: hasMany → Actividad

#### Scopes
- `activos()`: Usuarios activos
- `inactivos()`: Usuarios inactivos
- `conRol($rol)`: Filtra por rol

#### Métodos Útiles
- `esAdmin()`: Verifica si es admin
- `esSupervisor()`: Verifica si es supervisor
- `esOperario()`: Verifica si es operario
- `puedeCanjearPuntos()`: Admin o supervisor
- `puedeModificarConfiguracion()`: Admin o supervisor
- `getBadgeColorAttribute()`: Color según rol
- `getRolNombreAttribute()`: Nombre en español
- `rolesDisponibles()`: Array con todos los roles

---

### 3. Factura (`app/Models/Factura.php`) - 190 líneas

**Tabla:** `facturas`

#### Propiedades
```php
- cliente_id (integer, FK)
- numero_factura (string)
- monto_total (decimal)
- moneda (string: UYU, USD, EUR)
- puntos_generados (decimal)
- promocion_aplicada (string, nullable)
- payload_json (json)
- fecha_emision (datetime)
- fecha_vencimiento (datetime)
```

#### Relaciones
- `cliente()`: belongsTo → Cliente

#### Scopes
- `activas()`: No vencidas
- `vencidas()`: Vencidas
- `porVencer($dias = 30)`: Por vencer en N días
- `delMes()`: Del mes actual
- `conPromocion()`: Con promoción aplicada

#### Métodos Útiles
- `estaVencida()`: Verifica vencimiento
- `diasParaVencer()`: Días restantes
- `getMontoFormateadoAttribute()`: Monto con símbolo de moneda
- `getPuntosFormateadosAttribute()`: Puntos formateados
- `getBadgeEstadoAttribute()`: [class, text] según estado

---

### 4. PuntosCanjeado (`app/Models/PuntosCanjeado.php`) - 100 líneas

**Tabla:** `puntos_canjeados`

#### Propiedades
```php
- cliente_id (integer, FK)
- puntos_canjeados (decimal)
- puntos_restantes (decimal)
- concepto (string)
- autorizado_por (integer, FK)
```

#### Relaciones
- `cliente()`: belongsTo → Cliente
- `autorizadoPor()`: belongsTo → Usuario

#### Scopes
- `delMes()`: Del mes actual
- `hoy()`: De hoy
- `entreFechas($desde, $hasta)`: Entre fechas

#### Métodos Útiles
- `getPuntosFormateadosAttribute()`: Puntos formateados
- `getCodigoCuponAttribute()`: Código único (C-00000001)

---

### 5. PuntosVencido (`app/Models/PuntosVencido.php`) - 80 líneas

**Tabla:** `puntos_vencidos`

#### Propiedades
```php
- cliente_id (integer, FK)
- puntos_vencidos (decimal)
- motivo (string)
```

#### Relaciones
- `cliente()`: belongsTo → Cliente

#### Scopes
- `delMes()`: Del mes actual
- `entreFechas($desde, $hasta)`: Entre fechas

#### Métodos Útiles
- `getPuntosFormateadosAttribute()`: Puntos formateados

---

### 6. Promocion (`app/Models/Promocion.php`) - 230 líneas

**Tabla:** `promociones`

#### Propiedades
```php
- nombre (string)
- descripcion (text, nullable)
- tipo (enum: multiplicador, puntos_extra, descuento_canje)
- valor (decimal)
- condicion (json: monto_minimo, monto_maximo, dias_semana)
- fecha_inicio (datetime)
- fecha_fin (datetime)
- activa (boolean)
```

#### Constantes
```php
TIPO_MULTIPLICADOR = 'multiplicador'
TIPO_PUNTOS_EXTRA = 'puntos_extra'
TIPO_DESCUENTO_CANJE = 'descuento_canje'
```

#### Scopes
- `activas()`: Vigentes (activa + en rango de fechas)
- `vencidas()`: Pasadas
- `programadas()`: Futuras
- `tipo($tipo)`: Filtra por tipo

#### Métodos Útiles
- `estaVigente()`: Verifica si está vigente
- `aplicar($monto, $puntosBase)`: Aplica la promoción
- `cumpleCondiciones($monto)`: Valida condiciones (private)
- `getBadgeEstadoAttribute()`: [class, text] según estado
- `getTipoNombreAttribute()`: Nombre del tipo
- `getValorDescripcionAttribute()`: Valor formateado (x2, +100, -10%)
- `tiposDisponibles()`: Array con tipos disponibles

---

### 7. Configuracion (`app/Models/Configuracion.php`) - 160 líneas

**Tabla:** `configuracion`

#### Propiedades
```php
- key (string, unique)
- value (json)
- descripcion (string)
```

#### Constantes
```php
KEY_PUNTOS_POR_PESOS = 'puntos_por_pesos'
KEY_DIAS_VENCIMIENTO = 'dias_vencimiento'
KEY_CONTACTO = 'contacto'
KEY_EVENTOS_WHATSAPP = 'eventos_whatsapp'
```

#### Métodos Estáticos
- `get($key, $default)`: Obtiene valor
- `set($key, $value, $descripcion)`: Establece valor
- `getPuntosPorPesos()`: Devuelve config de puntos
- `getDiasVencimiento()`: Devuelve días de vencimiento
- `getContacto()`: Devuelve datos de contacto
- `getEventosWhatsApp()`: Devuelve eventos habilitados
- `eventoWhatsAppHabilitado($evento)`: Verifica si evento está activo
- `todas()`: Devuelve todas las configs como array

---

### 8. Actividad (`app/Models/Actividad.php`) - 150 líneas

**Tabla:** `actividades`

#### Propiedades
```php
- usuario_id (integer, FK, nullable)
- accion (string)
- descripcion (string)
- datos_json (json)
```

#### Constantes
```php
ACCION_LOGIN = 'login'
ACCION_LOGOUT = 'logout'
ACCION_CANJE = 'canje_puntos'
ACCION_FACTURA = 'factura_procesada'
ACCION_CLIENTE_CREADO = 'cliente_creado'
ACCION_CONFIG = 'configuracion_actualizada'
ACCION_PROMOCION = 'promocion_gestionada'
ACCION_USUARIO = 'usuario_gestionado'
```

#### Relaciones
- `usuario()`: belongsTo → Usuario

#### Scopes
- `hoy()`: De hoy
- `delMes()`: Del mes actual
- `entreFechas($desde, $hasta)`: Entre fechas
- `accion($accion)`: Filtra por acción
- `deUsuario($usuarioId)`: Filtra por usuario

#### Métodos Útiles
- `registrar($usuarioId, $accion, $descripcion, $datos)`: Crea actividad (static)
- `getIconoAttribute()`: Icono Bootstrap según acción
- `getColorAttribute()`: Color según acción

---

## 📊 Uso de los Modelos

### Ejemplos Prácticos

#### 1. Obtener clientes con puntos y sus facturas activas
```php
$clientes = Cliente::conPuntos()
    ->with('facturasActivas')
    ->orderBy('puntos_acumulados', 'desc')
    ->get();
```

#### 2. Buscar cliente y ver su historial
```php
$cliente = Cliente::where('documento', '12345678')->first();
$historial = $cliente->puntosCanjeados()->latest()->get();
$puntos_activos = $cliente->facturasActivas()->sum('puntos_generados');
```

#### 3. Obtener promociones vigentes y aplicar
```php
$promociones = Promocion::activas()->get();

foreach ($promociones as $promo) {
    if ($promo->estaVigente()) {
        $puntosFinales = $promo->aplicar($monto, $puntosBase);
    }
}
```

#### 4. Obtener configuración del tenant
```php
$puntosPorPesos = Configuracion::getPuntosPorPesos();
$diasVencimiento = Configuracion::getDiasVencimiento();
$contacto = Configuracion::getContacto();
```

#### 5. Registrar actividad
```php
Actividad::registrar(
    $usuarioId,
    Actividad::ACCION_CANJE,
    'Canje de 500 puntos realizado',
    ['cliente_id' => 123, 'puntos' => 500]
);
```

#### 6. Verificar permisos de usuario
```php
$usuario = Usuario::find(session('usuario_id'));

if ($usuario->puedeCanjearPuntos()) {
    // Permitir canje
}
```

#### 7. Facturas por vencer con notificación
```php
$facturas = Factura::porVencer(7)
    ->with('cliente')
    ->get();

foreach ($facturas as $factura) {
    if ($factura->diasParaVencer() <= 3) {
        // Enviar notificación urgente
    }
}
```

---

## 🔄 Dashboard Actualizado

El `DashboardController` ahora usa los modelos Eloquent:

**Antes:**
```php
$totalClientes = DB::table('clientes')->count();
```

**Ahora:**
```php
$totalClientes = Cliente::count();
$clientesActivos = Cliente::activos(30)->count();
$facturasMes = Factura::delMes()->count();
$puntosCanjeados = PuntosCanjeado::delMes()->sum('puntos_canjeados');
```

**Ventajas:**
- ✅ Código más limpio y legible
- ✅ Uso de scopes reutilizables
- ✅ Relaciones cargadas con `with()` (eager loading)
- ✅ Tipo-safe con objetos Eloquent
- ✅ Accessors para formateo automático

---

## 📝 Convenciones Seguidas

### Nombres de Métodos
- **Scopes**: `scopeNombre($query, ...)`
- **Relaciones**: `nombreRelacion()` (plural para many)
- **Accessors**: `getNombreAttribute()`
- **Helpers**: `nombreDescriptivo()` (sin prefijo get)

### Casteo de Datos
- `decimal:2` para puntos y montos
- `datetime` para fechas
- `array` para JSON
- `boolean` para flags

### Documentación
- Cada modelo tiene docblock completo
- Métodos documentados con `@param` y `@return`
- Explicación del propósito de cada scope

---

## ✅ Beneficios Implementados

1. **Código más limpio**: Queries legibles tipo `Cliente::activos()->conPuntos()`
2. **Reutilización**: Scopes disponibles en cualquier parte
3. **Relaciones fáciles**: `$cliente->facturas` automático
4. **Formateo automático**: `$cliente->puntos_formateados`
5. **Validaciones**: Métodos helper tipo `$factura->estaVencida()`
6. **Type hints**: IDE auto-completa propiedades y métodos
7. **Eager Loading**: Optimización con `with()` para evitar N+1

---

## 🎯 Próximos Pasos

Con los modelos listos, ahora podemos implementar fácilmente:

1. **Gestión de Clientes**: CRUD con búsqueda y filtros
2. **Sistema de Canje**: Validaciones y transacciones
3. **Portal Público**: Consulta simple de puntos
4. **Promociones**: Aplicación automática en webhook
5. **Reportes**: Queries complejas simplificadas

---

**Última actualización:** 2025-09-29
