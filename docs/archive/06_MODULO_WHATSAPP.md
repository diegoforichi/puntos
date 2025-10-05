# Módulo de Notificaciones WhatsApp

## Fecha: 2025-09-29

## 🎯 Objetivo

Integrar el servicio de WhatsApp existente con el sistema de puntos para enviar notificaciones automáticas a los clientes de cada comercio.

## 📱 Configuración del Servicio

### **Centro de Reparto Único (SuperAdmin)**

El SuperAdmin configura **un único servicio** para todos los tenants:

```php
// Tabla: system_config (base principal MySQL)
{
  "whatsapp_activo": true,              // Activar/desactivar WhatsApp globalmente
  "whatsapp_token": "abc123xyz789",     // Token único para todo el sistema
  "whatsapp_url": "https://6023-39939.el-alt.com/monitorwappapi/api/message/sendMessageAndUrlDocument",
  "codigo_pais": "+598",                // Código de país (Uruguay)
  "email_smtp_host": "smtp.gmail.com",
  "email_smtp_port": 587,
  "email_smtp_user": "sistema@tudominio.com",
  "email_smtp_pass": "password",
  "email_from": "sistema@tudominio.com"
}
```

### **Configuración por Tenant (Solo Datos de Contacto)**

Cada comercio solo configura sus **datos de contacto** y **preferencias**:

```php
// Tabla: configuracion (en cada base de tenant)
{
  "nombre_comercial": "Supermercado ACME",
  "telefono_contacto": "099123456",
  "direccion_contacto": "Av. Principal 1234, Montevideo",
  "email_contacto": "contacto@acme.com",
  "eventos_whatsapp": {
    "puntos_canjeados": true,
    "puntos_por_vencer": true,
    "promociones_activas": true,
    "bienvenida_nuevos": true
  },
  "eventos_email": {
    "resumen_semanal": true,
    "alertas_sistema": true
  }
}
```

### **Templates de Mensajes con Variables Dinámicas**

Los mensajes se generan con **variables dinámicas** que se rellenan automáticamente:

```php
// Templates base del sistema (configurables por SuperAdmin)
{
  "evento": "puntos_canjeados",
  "template": "¡Hola **{nombre}**! Has canjeado **{puntos} puntos** en **{comercio}**. ¡Gracias por tu preferencia! 🎉\n\nContacto: **{telefono_contacto}** | **{direccion_contacto}**",
  "activo": true
},
{
  "evento": "puntos_por_vencer", 
  "template": "Hola **{nombre}**, tienes **{puntos} puntos** en **{comercio}** que vencen el **{fecha_vencimiento}**. ¡No los pierdas! ⏰\n\nSi quieres ponerte en contacto con nosotros:\n📞 **{telefono_contacto}**\n📍 **{direccion_contacto}**",
  "activo": true
},
{
  "evento": "promociones_activas",
  "template": "¡Hola **{nombre}**! **{comercio}** tiene una promoción especial: **{descripcion_promocion}**. ¡Aprovéchala! 🔥\n\nMás info: **{telefono_contacto}**",
  "activo": true
},
{
  "evento": "bienvenida_nuevos",
  "template": "¡Bienvenido **{nombre}** a **{comercio}**! Ya tienes **{puntos} puntos** acumulados. ¡Sigue comprando y acumula más! 🎁\n\nContacto: **{telefono_contacto}** | **{direccion_contacto}**",
  "activo": true
}
```

### **Variables Disponibles**

Todas las variables se rellenan automáticamente desde los datos del tenant y cliente:

- `{nombre}`: Nombre del cliente
- `{puntos}`: Cantidad de puntos
- `{comercio}`: Nombre comercial del tenant
- `{telefono_contacto}`: Teléfono del comercio
- `{direccion_contacto}`: Dirección del comercio
- `{email_contacto}`: Email del comercio
- `{fecha_vencimiento}`: Fecha de vencimiento de puntos
- `{dias}`: Días restantes para vencimiento
- `{descripcion_promocion}`: Descripción de la promoción activa

## 🔧 Implementación Técnica

### **Servicio de WhatsApp (WhatsAppService.php)**

```php
class WhatsAppService
{
    public function enviarNotificacion($cliente, $evento, $datos = [])
    {
        // 1. Verificar si WhatsApp está activo para este tenant
        $config = $this->getConfiguracionWhatsApp();
        if (!$config->whatsapp_activo) {
            return false;
        }

        // 2. Verificar si el evento está activo
        if (!$config->eventos_activos[$evento]) {
            return false;
        }

        // 3. Obtener template del mensaje
        $template = $this->getTemplate($evento);
        if (!$template) {
            return false;
        }

        // 4. Formatear número de teléfono
        $numeroFormateado = $this->formatearNumero($cliente->telefono, $config->codigo_pais);
        if (!$numeroFormateado) {
            Log::warning("Número de teléfono inválido: {$cliente->telefono}");
            return false;
        }

        // 5. Generar mensaje personalizado
        $mensaje = $this->generarMensaje($template, $cliente, $datos);

        // 6. Enviar WhatsApp
        return $this->enviarWhatsApp($numeroFormateado, $mensaje, $config);
    }

    private function formatearNumero($telefono, $codigoPais)
    {
        // Limpiar el número (solo dígitos)
        $numero = preg_replace('/[^0-9]/', '', $telefono);
        
        // Validar que tenga el formato correcto (ej: 098574709)
        if (strlen($numero) !== 9 || !preg_match('/^09[0-9]{7}$/', $numero)) {
            return false;
        }

        // Convertir 098574709 a +59898574709
        $numeroSinCero = substr($numero, 1); // Quitar el 0 inicial
        return $codigoPais . $numeroSinCero;
    }

    private function enviarWhatsApp($numero, $mensaje, $config)
    {
        $url = $config->whatsapp_url . '?' . http_build_query([
            'token' => $config->whatsapp_token,
            'number' => $numero,
            'message' => $mensaje,
            'urlDocument' => '' // Por ahora vacío
        ]);

        try {
            $response = Http::timeout(30)->get($url);
            
            if ($response->successful()) {
                $this->logEnvio($numero, $mensaje, 'exitoso');
                return true;
            } else {
                $this->logEnvio($numero, $mensaje, 'fallido', $response->status());
                $this->programarReintento($numero, $mensaje);
                return false;
            }
        } catch (Exception $e) {
            $this->logEnvio($numero, $mensaje, 'error', $e->getMessage());
            $this->programarReintento($numero, $mensaje);
            return false;
        }
    }
}
```

### **Sistema de Reintentos**

```php
// Tabla: whatsapp_queue (en cada base de tenant)
CREATE TABLE whatsapp_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero VARCHAR(20) NOT NULL,
    mensaje TEXT NOT NULL,
    evento VARCHAR(50) NOT NULL,
    intentos INT DEFAULT 0,
    max_intentos INT DEFAULT 3,
    proximo_intento TIMESTAMP,
    estado ENUM('pendiente', 'enviado', 'fallido') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🎛️ Panel de Configuración

### **Panel SuperAdmin - Configuración Global**

El SuperAdmin configura el servicio único para todo el sistema:

```html
<!-- Vista: superadmin/configuracion-global.blade.php -->
<div class="card">
    <div class="card-header">
        <h5>Configuración Global - WhatsApp y Email</h5>
    </div>
    <div class="card-body">
        <h6>Servicio WhatsApp</h6>
        <div class="mb-3">
            <label for="whatsapp_token">Token del Servicio WhatsApp</label>
            <input type="text" id="whatsapp_token" name="whatsapp_token" class="form-control">
        </div>
        <div class="mb-3">
            <label for="whatsapp_url">URL del Servicio</label>
            <input type="url" id="whatsapp_url" name="whatsapp_url" class="form-control" 
                   value="https://6023-39939.el-alt.com/monitorwappapi/api/message/sendMessageAndUrlDocument">
        </div>
        <div class="mb-3">
            <label for="codigo_pais">Código de País</label>
            <input type="text" id="codigo_pais" name="codigo_pais" value="+598" class="form-control">
        </div>

        <hr>

        <h6>Configuración SMTP</h6>
        <div class="row">
            <div class="col-md-6">
                <label for="smtp_host">Servidor SMTP</label>
                <input type="text" id="smtp_host" name="smtp_host" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="smtp_port">Puerto</label>
                <input type="number" id="smtp_port" name="smtp_port" value="587" class="form-control">
            </div>
        </div>
        <div class="mb-3">
            <label for="email_from">Email Remitente</label>
            <input type="email" id="email_from" name="email_from" class="form-control" 
                   placeholder="sistema@tudominio.com">
        </div>
    </div>
</div>
```

### **Panel Tenant - Datos de Contacto y Preferencias**

Cada comercio configura sus datos de contacto y qué notificaciones quiere recibir:

```html
<!-- Vista: configuracion/contacto-notificaciones.blade.php -->
<div class="card">
    <div class="card-header">
        <h5>Datos de Contacto y Notificaciones</h5>
    </div>
    <div class="card-body">
        <h6>Información del Comercio</h6>
        <div class="mb-3">
            <label for="nombre_comercial">Nombre Comercial</label>
            <input type="text" id="nombre_comercial" name="nombre_comercial" 
                   class="form-control" placeholder="Supermercado ACME">
        </div>
        <div class="mb-3">
            <label for="telefono_contacto">Teléfono de Contacto</label>
            <input type="text" id="telefono_contacto" name="telefono_contacto" 
                   class="form-control" placeholder="099123456">
        </div>
        <div class="mb-3">
            <label for="direccion_contacto">Dirección</label>
            <input type="text" id="direccion_contacto" name="direccion_contacto" 
                   class="form-control" placeholder="Av. Principal 1234, Montevideo">
        </div>
        <div class="mb-3">
            <label for="email_contacto">Email de Contacto</label>
            <input type="email" id="email_contacto" name="email_contacto" 
                   class="form-control" placeholder="contacto@acme.com">
        </div>

        <hr>

        <h6>Notificaciones WhatsApp a Clientes</h6>
        <div class="form-check">
            <input type="checkbox" id="evento_canjes" name="eventos_whatsapp[puntos_canjeados]">
            <label for="evento_canjes">Cuando se canjean puntos</label>
        </div>
        <div class="form-check">
            <input type="checkbox" id="evento_vencer" name="eventos_whatsapp[puntos_por_vencer]">
            <label for="evento_vencer">Cuando los puntos están por vencer</label>
        </div>
        <div class="form-check">
            <input type="checkbox" id="evento_promociones" name="eventos_whatsapp[promociones_activas]">
            <label for="evento_promociones">Cuando hay promociones activas</label>
        </div>
        <div class="form-check">
            <input type="checkbox" id="evento_bienvenida" name="eventos_whatsapp[bienvenida_nuevos]">
            <label for="evento_bienvenida">Bienvenida a nuevos clientes</label>
        </div>

        <hr>

        <h6>Notificaciones Email al Administrador</h6>
        <div class="form-check">
            <input type="checkbox" id="resumen_semanal" name="eventos_email[resumen_semanal]">
            <label for="resumen_semanal">Resumen semanal de actividad</label>
        </div>
        <div class="form-check">
            <input type="checkbox" id="alertas_sistema" name="eventos_email[alertas_sistema]">
            <label for="alertas_sistema">Alertas del sistema</label>
        </div>
    </div>
</div>
```

### **Editor de Templates de Mensajes**

```html
<!-- Vista: configuracion/whatsapp-templates.blade.php -->
<div class="card">
    <div class="card-header">
        <h5>Personalizar Mensajes WhatsApp</h5>
    </div>
    <div class="card-body">
        <div class="accordion" id="templatesAccordion">
            <!-- Template: Puntos Canjeados -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#template1">
                        Mensaje: Puntos Canjeados
                    </button>
                </h2>
                <div id="template1" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        <textarea name="templates[puntos_canjeados]" class="form-control" rows="3">
¡Hola {nombre}! Has canjeado {puntos} puntos en {comercio}. ¡Gracias por tu preferencia! 🎉
                        </textarea>
                        <small class="text-muted">
                            Variables disponibles: {nombre}, {puntos}, {comercio}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Template: Puntos por Vencer -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#template2">
                        Mensaje: Puntos por Vencer
                    </button>
                </h2>
                <div id="template2" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <textarea name="templates[puntos_por_vencer]" class="form-control" rows="3">
Hola {nombre}, tienes {puntos} puntos en {comercio} que vencen en {dias} días. ¡No los pierdas! ⏰
                        </textarea>
                        <small class="text-muted">
                            Variables disponibles: {nombre}, {puntos}, {comercio}, {dias}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

### **Panel de Pruebas de WhatsApp**

```html
<!-- Vista: configuracion/whatsapp-test.blade.php -->
<div class="card">
    <div class="card-header">
        <h5>Probar Envío de WhatsApp</h5>
    </div>
    <div class="card-body">
        <form id="testWhatsAppForm">
            <div class="mb-3">
                <label for="test_numero">Número de Teléfono</label>
                <input type="text" id="test_numero" name="numero" placeholder="098574709" class="form-control">
                <small class="text-muted">Formato: 098574709 (se convertirá automáticamente a +59898574709)</small>
            </div>
            <div class="mb-3">
                <label for="test_mensaje">Mensaje de Prueba</label>
                <textarea id="test_mensaje" name="mensaje" class="form-control" rows="3">
Hola, este es un mensaje de prueba desde {comercio}. ¡El sistema WhatsApp está funcionando correctamente! ✅
                </textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar Prueba</button>
        </form>
        <div id="testResult" class="mt-3"></div>
    </div>
</div>
```

## 🔄 Disparadores de Eventos

### **Eventos que Envían WhatsApp**

```php
// 1. Al canjear puntos (PuntosController.php)
public function canjearPuntos(Request $request)
{
    // ... lógica de canje ...
    
    // Enviar WhatsApp
    $whatsappService = new WhatsAppService();
    $whatsappService->enviarNotificacion($cliente, 'puntos_canjeados', [
        'puntos' => $puntosCanjeados,
        'comercio' => $this->getTenantName()
    ]);
}

// 2. Puntos por vencer (Comando Cron)
public function verificarPuntosProximosAVencer()
{
    $clientesConPuntosProximos = Cliente::where('puntos_acumulados', '>', 0)
        ->where('ultima_actividad', '<', now()->subDays($diasVencimiento - 7))
        ->get();

    foreach ($clientesConPuntosProximos as $cliente) {
        $whatsappService->enviarNotificacion($cliente, 'puntos_por_vencer', [
            'puntos' => $cliente->puntos_acumulados,
            'dias' => 7,
            'comercio' => $this->getTenantName()
        ]);
    }
}

// 3. Bienvenida a nuevos clientes (WebhookController.php)
public function procesarFactura(Request $request)
{
    // ... lógica de procesamiento ...
    
    // Si es un cliente nuevo
    if ($esClienteNuevo) {
        $whatsappService->enviarNotificacion($cliente, 'bienvenida_nuevos', [
            'puntos' => $puntosGenerados,
            'comercio' => $this->getTenantName()
        ]);
    }
}
```

## 📊 Logs y Monitoreo

### **Tabla de Logs de WhatsApp**

```sql
CREATE TABLE whatsapp_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,
    numero VARCHAR(20),
    evento VARCHAR(50),
    mensaje TEXT,
    estado ENUM('enviado', 'fallido', 'pendiente'),
    codigo_respuesta INT,
    error_mensaje TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);
```

### **Dashboard de WhatsApp**

Panel para que cada comercio vea:
- Total de WhatsApp enviados hoy/semana/mes
- Tasa de éxito de envíos
- Últimos mensajes enviados
- Mensajes fallidos para revisar

## 🔧 Tareas Programadas

### **Procesamiento de Cola de Reintentos**

```php
// Comando Cron: php artisan whatsapp:procesar-cola
public function procesarColaWhatsApp()
{
    $mensajesPendientes = WhatsAppQueue::where('estado', 'pendiente')
        ->where('proximo_intento', '<=', now())
        ->where('intentos', '<', 'max_intentos')
        ->get();

    foreach ($mensajesPendientes as $mensaje) {
        $resultado = $this->reintentarEnvio($mensaje);
        
        if ($resultado) {
            $mensaje->update(['estado' => 'enviado']);
        } else {
            $mensaje->increment('intentos');
            
            if ($mensaje->intentos >= $mensaje->max_intentos) {
                $mensaje->update(['estado' => 'fallido']);
            } else {
                // Programar siguiente intento en 1 hora
                $mensaje->update([
                    'proximo_intento' => now()->addHour()
                ]);
            }
        }
    }
}
```

## ✅ Validaciones y Seguridad

### **Validación de Números de Teléfono**

```php
public function validarNumeroUruguayo($telefono)
{
    // Limpiar número
    $numero = preg_replace('/[^0-9]/', '', $telefono);
    
    // Validar formato uruguayo: 09XXXXXXX
    if (strlen($numero) === 9 && preg_match('/^09[0-9]{7}$/', $numero)) {
        return true;
    }
    
    return false;
}
```

### **Rate Limiting**

- Máximo 100 WhatsApp por hora por tenant
- Pausa de 1 segundo entre envíos
- Queue para manejar picos de envío

### **Seguridad del Token**

- Tokens encriptados en base de datos
- Validación de tokens en cada envío
- Logs de todos los intentos de envío
