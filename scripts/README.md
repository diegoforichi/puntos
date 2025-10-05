# Scripts de Prueba - Sistema de Puntos

## 📋 Contenido

- **`emulador_webhook.php`**: Emulador de webhook para pruebas del sistema de eFactura

---

## 🚀 Emulador de Webhook

### Descripción

Script PHP standalone que simula el envío de facturas desde un sistema de eFactura al webhook de Laravel. Lee el archivo `hookCfe.json` de referencia y genera datos aleatorios para pruebas realistas.

### Requisitos

- PHP 7.4+ con extensión `curl`
- Archivo `hookCfe.json` en la raíz del proyecto

### Uso Básico

```bash
# Enviar 1 factura al webhook local
php scripts/emulador_webhook.php

# Enviar 5 facturas
php scripts/emulador_webhook.php --cantidad=5

# Ver todas las opciones
php scripts/emulador_webhook.php --help
```

### Opciones Disponibles

| Opción | Descripción | Default |
|--------|-------------|---------|
| `--url=URL` | URL del webhook de destino | `http://localhost:8000/api/webhook/ingest` |
| `--rut=RUT` | RUT del emisor/tenant | `000000000016` |
| `--api-key=KEY` | API Key del tenant | `test-api-key-demo` |
| `--cantidad=N` | Número de facturas a generar | `1` |
| `--sin-telefono` | Simular cliente sin teléfono | `false` |
| `--rut-incorrecto` | Enviar con RUT que no existe | `false` |
| `--api-key-mala` | Enviar con API Key incorrecta | `false` |
| `--help` | Mostrar ayuda | - |

### Datos Generados Aleatoriamente

El emulador genera valores aleatorios para:

- **Número de factura**: 80000-90000
- **Fecha de emisión**: Fecha y hora actual
- **Cliente**:
  - Documento: Cédula uruguaya válida (8 dígitos)
  - Nombre: Combinación aleatoria de nombres y apellidos
  - Teléfono: Formato uruguayo `09XXXXXXX` (opcional)
  - Email: Generado a partir del nombre (50% probabilidad)
- **Monto total**: $500 - $50,000 (con IVA 22%)
- **Producto**: Selección aleatoria de catálogo predefinido

### Ejemplos de Uso

#### Prueba Local Básica

```bash
php scripts/emulador_webhook.php
```

**Salida esperada:**
```
🚀 Emulador de Webhook - Sistema de Puntos
============================================================

📡 URL destino: http://localhost:8000/api/webhook/ingest
🏢 RUT Emisor: 000000000016
🔑 API Key: test-api...
📊 Cantidad: 1 factura(s)

------------------------------------------------------------

📄 Generando factura #1...
✅ Factura #1 enviada correctamente
   Número: 85432, Cliente: 41234567, Monto: $12450.50

============================================================
📊 Resumen:
   ✅ Exitosos: 1
   ❌ Fallidos: 0
============================================================
```

#### Prueba de Volumen

```bash
php scripts/emulador_webhook.php --cantidad=10
```

Envía 10 facturas con pausa de 1 segundo entre cada una.

#### Prueba de Cliente sin Teléfono

```bash
php scripts/emulador_webhook.php --sin-telefono
```

Útil para probar el flujo de captura de teléfono en el portal de autoconsulta.

#### Prueba de Errores de Seguridad

```bash
# API Key incorrecta
php scripts/emulador_webhook.php --api-key-mala

# RUT que no existe
php scripts/emulador_webhook.php --rut-incorrecto
```

Útil para verificar las respuestas de error del sistema y el registro en `webhook_inbox`.

#### Prueba con Servidor Remoto (Producción/Staging)

```bash
php scripts/emulador_webhook.php \
  --url=https://midominio.com/api/webhook/ingest \
  --rut=210010020030 \
  --api-key=produccion-api-key-real \
  --cantidad=3
```

#### Prueba con Túnel Local (ngrok)

```bash
# 1. Iniciar túnel
ngrok http 8000

# 2. Usar la URL generada
php scripts/emulador_webhook.php \
  --url=https://abc123.ngrok.io/api/webhook/ingest
```

### Flujo de Trabajo Recomendado

1. **Desarrollo Local**:
   ```bash
   # Terminal 1: Iniciar Laravel
   php artisan serve
   
   # Terminal 2: Enviar factura de prueba
   php scripts/emulador_webhook.php
   ```

2. **Pruebas de Integración**:
   ```bash
   # Enviar 5 facturas variadas
   php scripts/emulador_webhook.php --cantidad=5
   
   # Verificar en bandeja de entrada del panel
   # http://localhost:8000/{tenant}/integraciones/inbox
   ```

3. **Pruebas de Errores**:
   ```bash
   # Probar diferentes escenarios de error
   php scripts/emulador_webhook.php --api-key-mala
   php scripts/emulador_webhook.php --rut-incorrecto
   php scripts/emulador_webhook.php --sin-telefono
   ```

4. **Validación con Cliente Real**:
   ```bash
   # Usar ngrok para exponer webhook local
   ngrok http 8000
   
   # Configurar URL en sistema de eFactura real
   # (o usar el emulador con datos similares)
   ```

### Integración con Laravel

Una vez que Laravel esté configurado, este script se puede migrar a un comando Artisan:

```bash
php artisan webhook:simular --tenant=demo --cantidad=5
```

El comando interno usará el mismo código de generación de datos pero invocará directamente el servicio de procesamiento sin pasar por HTTP.

### Troubleshooting

**Error: "No se encontró el archivo hookCfe.json"**
- Verificar que el archivo `hookCfe.json` existe en la raíz del proyecto
- Ejecutar el script desde la raíz: `php scripts/emulador_webhook.php`

**Error de conexión cURL**
- Verificar que Laravel está corriendo (`php artisan serve`)
- Verificar que la URL es correcta (puerto 8000 por defecto)
- Si usa HTTPS, verificar certificados

**Código HTTP 404**
- El webhook aún no está implementado en Laravel
- Verificar que la ruta `/api/webhook/ingest` está registrada

**Código HTTP 401/403**
- API Key incorrecta o no configurada en el tenant
- Verificar configuración de API Keys en base de datos

---

## 🔮 Scripts Futuros

Otros scripts que se agregarán en fases posteriores:

- `backup_manual.php`: Backup manual de todas las bases de datos
- `limpiar_datos_antiguos.php`: Limpieza de datos históricos
- `generar_tenant.php`: Creación de tenant desde línea de comandos
- `health_check.php`: Verificación de estado del sistema

---

## 📝 Notas

- Todos los scripts están diseñados para ser ejecutados desde la raíz del proyecto
- Los datos generados son completamente ficticios y no representan información real
- El emulador respeta el formato exacto del JSON de eFactura para máxima compatibilidad
