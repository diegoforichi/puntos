# Funcionalidades y Requisitos del Sistema

## Fecha: 2025-09-29

## 🎯 Objetivo del Sistema

Desarrollar un sistema de gestión de puntos multitenant que permita a múltiples comercios gestionar independientemente los puntos de fidelidad de sus clientes, con integración automática a sistemas de eFactura y notificaciones por WhatsApp/Email.

## 👥 Roles y Permisos del Sistema

### **SuperAdmin (Administrador del Sistema)**
- ✅ **Gestión de Tenants**: Crear, suspender, eliminar comercios
- ✅ **Configuración Global**: WhatsApp, Email, retención de datos
- ✅ **Monitoreo**: Logs globales, estadísticas del sistema
- ✅ **Backup**: Gestión de respaldos automáticos
- ❌ **NO accede**: A datos específicos de puntos de cada comercio

### **Admin (Administrador del Comercio)**
- ✅ **Control Total**: Todas las funciones dentro de su tenant
- ✅ **Configuración**: Puntos por pesos, vencimiento, promociones
- ✅ **Usuarios**: Crear supervisores y operarios
- ✅ **Canje**: Canjear puntos sin autorización
- ✅ **Reportes**: Todos los reportes y estadísticas

### **Supervisor (Supervisor del Comercio)**
- ✅ **Canje**: Canjear puntos sin autorización
- ✅ **Autorización**: Autorizar canjes de operarios (con contraseña)
- ✅ **Configuración**: Modificar parámetros básicos
- ✅ **Reportes**: Ver reportes básicos
- ❌ **NO puede**: Crear usuarios, eliminar datos, configurar promociones

### **Operario (Usuario Básico del Comercio)**
- ✅ **Consulta**: Ver puntos de clientes
- ✅ **Canje con Autorización**: Requiere contraseña de supervisor/admin
- ❌ **NO puede**: Canjear sin autorización, modificar configuración, ver reportes

## 🎫 Sistema de Gestión de Puntos

### **Acumulación de Puntos**
- **Origen**: Facturas recibidas por webhook desde sistemas de eFactura
- **Cálculo**: `monto_factura / puntos_por_pesos` (configurable por tenant)
- **Promociones**: Multiplicadores, puntos extra o descuentos aplicables
- **Registro**: Todas las facturas se guardan como referencia

### **Canje de Puntos**
- **Tipos**: Canje total o parcial de puntos
- **Autorización Simple**: 
  - Admin/Supervisor: Canje directo
  - Operario: Requiere contraseña de supervisor/admin
- **Confirmación Digital**: Pantalla con detalles del canje (no impresión)
- **Notificación**: WhatsApp automático al cliente (si tiene teléfono)

### **Vencimiento de Puntos**
- **Configuración**: Días de vencimiento por tenant
- **Proceso**: Cron diario elimina puntos vencidos
- **Notificación**: WhatsApp 7 días antes del vencimiento
- **Registro**: Puntos vencidos se guardan en histórico

## 🎁 Sistema de Promociones

### **Tipos de Promociones**
1. **Multiplicador**: "Miércoles puntos dobles" (x2, x3, etc.)
2. **Puntos Extra**: "100 puntos extra en compras > $5000"
3. **Descuento en Canje**: "20% descuento en canjes esta semana"

### **Configuración Simple**
- **Dropdowns**: Día de semana, tipo de promoción, valor
- **Fechas**: Inicio y fin de la promoción
- **Condiciones**: Monto mínimo, día específico
- **Regla**: Solo una promoción por factura (no se combinan)

### **Aplicación Automática**
1. Calcular puntos base de la factura
2. Buscar promociones activas para la fecha
3. Aplicar la primera promoción que cumpla condiciones
4. Si no hay promociones, usar puntos base

## 👤 Portal de Autoconsulta Pública

### **Acceso**
- **URL**: `dominio.com/{tenant}/consulta`
- **Sin Login**: Solo requiere documento del cliente
- **Responsive**: Optimizado para móviles

### **Información Mostrada**
```
¡Hola Juan Pérez!
Tienes 1,250 puntos acumulados en Supermercado ACME

📅 Última compra: 15/12/2024
⏰ Tus puntos vencen: 15/06/2025 (180 días restantes)

📞 Para canjear tus puntos, contacta:
Teléfono: 099123456
Dirección: Av. Principal 1234

[Botón: Actualizar mi teléfono para WhatsApp]
```

### **Captura de Teléfono**
- Formulario opcional para recibir notificaciones WhatsApp
- Se guarda en la base de datos del tenant
- Usado para notificaciones futuras

## 📱 Sistema de Notificaciones

### **Centro de Reparto Único**
- **WhatsApp**: Un solo token para todo el sistema
- **Email**: Una sola configuración SMTP
- **Configuración**: Solo el SuperAdmin configura servicios
- **Personalización**: Mensajes con variables por tenant

### **Eventos de WhatsApp**
- **Puntos Canjeados**: Confirmación inmediata
- **Puntos por Vencer**: Alerta 7 días antes
- **Promociones Activas**: Cuando se activa una promoción
- **Bienvenida**: Para clientes nuevos

### **Templates Personalizables**
```
¡Hola **{nombre}**! Has canjeado **{puntos} puntos** en **{comercio}**. 
¡Gracias por tu preferencia! 🎉

Contacto: **{telefono_contacto}** | **{direccion_contacto}**
```

### **Variables Disponibles**
- `{nombre}`: Nombre del cliente
- `{puntos}`: Cantidad de puntos
- `{comercio}`: Nombre comercial del tenant
- `{telefono_contacto}`: Teléfono del comercio
- `{direccion_contacto}`: Dirección del comercio
- `{fecha_vencimiento}`: Fecha de vencimiento

## 📈 Dashboard y Estadísticas

### **Métricas del Mes Actual**
- Total puntos generados
- Puntos canjeados
- Nuevos clientes registrados
- Facturas procesadas

### **Comparativas**
- Vs mes anterior (% crecimiento/decrecimiento)
- Tendencias de los últimos 6 meses
- Picos de actividad por día/hora

### **Top Rankings**
- Top 5 clientes con más puntos
- Días con más actividad
- Productos/servicios más populares

### **Alertas**
- Puntos próximos a vencer (próximos 7 días)
- Clientes inactivos (sin compras en X días)
- Errores en el sistema

## 🔔 Centro de Notificaciones

### **Alertas en el Panel**
Ícono de campana 🔔 en la esquina del panel que muestra:

- **Errores de WhatsApp**: "No se pudo enviar WhatsApp a cliente X (número inválido)"
- **Errores de Email**: "No se pudo enviar resumen semanal (email inválido)"
- **Webhook**: "Se recibieron 5 facturas con errores en las últimas 24h"
- **Configuración**: "Tu configuración de puntos por peso está en 0 (revísala)"

### **Estados de Notificaciones**
- **Nueva**: Requiere atención
- **Leída**: Vista pero no resuelta
- **Resuelta**: Problema solucionado

## 📊 Reportes y Exportación

### **Reportes Disponibles**
- **Clientes**: Lista con puntos, última actividad, contacto
- **Canjes**: Histórico de canjes por fecha, usuario, cliente
- **Facturas**: Facturas procesadas con puntos generados
- **Vencidos**: Puntos vencidos por cliente y fecha
- **Actividad**: Log de acciones por usuario

### **Formatos de Exportación**
- **CSV**: Para análisis en Excel
- **PDF**: Para reportes impresos
- **Excel**: Con formato y gráficos básicos

### **Filtros Avanzados**
- Por rango de fechas
- Por cliente específico
- Por usuario que realizó la acción
- Por tipo de transacción

## 🔧 Gestión de Tenants

### **Creación de Tenant**
**Formulario del SuperAdmin:**
- RUT del comercio
- Nombre comercial
- Contacto (nombre, email, teléfono)
- Dirección del comercio

**Proceso Automático:**
1. Validar que el RUT no exista
2. Generar API Key única
3. Crear base SQLite del tenant
4. Ejecutar migraciones
5. Crear usuario admin inicial
6. Enviar credenciales por email

### **Estados de Tenant**
- **Activo**: Funcionando normalmente
- **Suspendido**: Webhook bloqueado, usuarios no pueden acceder
- **Eliminado**: Marcado para eliminación (datos se mantienen 30 días)

## 💾 Gestión de Datos

### **Backup Automático**
- **Frecuencia**: Diario a las 02:00 AM
- **Retención**: 30 días
- **Contenido**: Todas las bases SQLite + base principal MySQL
- **Formato**: Comprimido con gzip
- **Ubicación**: `storage/backups/FECHA/`

### **Retención de Datos Históricos**
**Configuración Global (SuperAdmin):**
- Eliminar registros después de X años (1, 2, 3, 5 años o nunca)
- **Aplica a**: `puntos_canjeados`, `puntos_vencidos`, `actividades`, `whatsapp_logs`, `facturas`
- **Proceso**: Cron diario junto con backup
- **Notificación**: Panel administrativo informa sobre eliminaciones

### **Eliminación de Facturas de Referencia**
- **Al canjear puntos**: Eliminar facturas que generaron esos puntos (FIFO)
- **Al vencer puntos**: Eliminar facturas asociadas
- **Objetivo**: Mantener solo facturas de puntos activos
- **Retención**: 1 año mínimo antes de eliminación automática

## 🔒 Seguridad y Validaciones

### **Webhook Security**
- **API Key**: Bearer token único por tenant
- **Validación**: RUT + API Key matching
- **Rate Limiting**: 100 requests/minuto por tenant
- **Logging**: Todas las peticiones registradas

### **Formateo de Números WhatsApp**
- **Input**: `098574709` (formato uruguayo)
- **Output**: `+59898574709` (formato internacional)
- **Validación**: Solo números uruguayos válidos (09XXXXXXX)
- **Error**: Log para números inválidos

### **Autenticación**
- **Por Tenant**: Usuarios independientes por comercio
- **Encriptación**: Contraseñas con bcrypt
- **Sesiones**: Laravel Sanctum
- **Rate Limiting**: Protección contra ataques de fuerza bruta

## 🎯 Criterios de Éxito

### **Performance**
- Webhook: < 500ms respuesta
- Interfaz web: < 2s carga de páginas
- Soporte: 50+ usuarios concurrentes por tenant
- Disponibilidad: 99.9% uptime

### **Usabilidad**
- Interfaz intuitiva similar al sistema anterior
- Responsive en todos los dispositivos
- Mensajes de error claros y accionables
- Documentación completa para usuarios

### **Mantenimiento**
- Código bien documentado y modular
- Logs suficientes para debugging
- Backup automático y verificado
- Proceso de actualización sin downtime
