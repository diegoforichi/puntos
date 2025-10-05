# 📘 Manual de Usuario – Sistema de Puntos
**Versión:** 1.1 • **Fecha:** 02/10/2025

---

## 1. Tipos de Acceso

| Rol | URL | Base de datos | Credenciales iniciales |
|-----|-----|----------------|------------------------|
| **SuperAdmin** | `/superadmin/login` | `users` (MySQL) | `superadmin@puntos.local / superadmin123` |
| **Comercio (tenant demo)** | `/000000000016/login` | `usuarios` (SQLite tenant) | Usuarios iniciales auto-generados (`adminXXXX`, `supervisorXXXX`, `operarioXXXX` / `Admin123!`, `Supervisor123!`, `Operario123!`) |

- El **SuperAdmin** administra la plataforma, crea/suspende tenants y define configuraciones globales.
- Cada **comercio (tenant)** usa su propio panel con usuarios y datos aislados. No comparten credenciales con el SuperAdmin.

---

## 2. Panel SuperAdmin (`/superadmin`)

### 2.1 Dashboard Global
- Tarjetas: Tenants totales/activos, facturas procesadas, puntos generados, último webhook.
- Ranking “Tenants con mayor actividad”.
- Últimas acciones registradas en `admin_logs` (auditoría de cambios globales).

### 2.2 Configuración Global
- **Email SMTP:** servidor, puerto, usuario, contraseña, cifrado (ninguno/TLS/SSL), remitente. Si se deja la contraseña vacía mantiene la anterior.
  - **Botón "Enviar email de prueba"**: abre modal para ingresar email destino y validar la configuración.
- **WhatsApp:** endpoint/URL, token, código país (+598 default), switch "Servicio habilitado".
  - **Botón "Enviar WhatsApp de prueba"**: abre modal para ingresar número de teléfono y probar el envío.
- Toda actualización queda registrada en `admin_logs`.

### 2.3 Gestión de Tenants
- Crear tenant (RUT, nombre comercial, datos de contacto). Al guardar se crea el archivo SQLite, se ejecutan migraciones y se muestran credenciales iniciales.
- Editar estado (`activo`, `suspendido`, `eliminado`), formato de factura, datos de contacto.
- Acciones rápidas: regenerar API Key, suspender/activar, regenerar usuarios iniciales (botón con ícono de personas). El mensaje confirma sufijo y si la base se inicializó.

### 2.4 Webhook Inbox Global
- Tabla con todos los registros de `webhook_inbox_global` (tenant, estado, http_status, mensaje de error, timestamps).
- Payload JSON expandible y formateado para análisis.

---

## 3. Panel del Comercio (`/{rut}`)

### 3.1 Navegación
- Sidebar responsive (botón hamburguesa en móvil) con módulos según rol.
- Login acepta **usuario o email**.
- Usuarios iniciales se generan automáticamente al crear el tenant. Se pueden regenerar desde el panel SuperAdmin (recomendado documentar/compartir). No se fuerza cambio de contraseña.

### 3.2 Dashboard del Comercio
- Métricas en tiempo real (clientes, puntos generados/canjeados, facturas del mes).
- Listado de clientes recientes y actividad reciente del tenant (últimas 5 entradas).

### 3.3 Gestión de Clientes
- Listado con búsqueda (documento/nombre), filtros (todos, con puntos, activos) y ordenamientos.
- Paginación Bootstrap (10 resultados por página) con indicador “Mostrando X–Y de Z”.
- Detalle: perfil, puntos disponibles, facturas activas, historial de canjes, puntos vencidos.
- Edición (Admin/Supervisor): nombre, email, teléfono, dirección.

### 3.4 Canje de Puntos (Admin/Supervisor)
1. Buscar cliente por documento.
2. Revisar puntos disponibles y facturas asociadas.
3. Seleccionar cantidad (botones rápidos 25/50/75/100% o manual).
4. Confirmar → se aplica FIFO, se registra en `puntos_canjeados`, se genera cupón digital con código y usuario responsable.

### 3.5 Promociones (Admin)
- Tipos: `descuento`, `bonificación`, `multiplicador`.
- Campos: nombre, descripción, valor, fechas, prioridad, condiciones (JSON almacenado en BD), estado (activa/inactiva).
- CRUD completo, audit trail en `actividades`.
- El `PuntosService` aplica automáticamente la promoción de mayor prioridad que cumpla condiciones.

### 3.6 Reportes y CSV
- Reportes disponibles: clientes, facturas, canjes, actividades.
- Filtros por fechas, estado, usuario según reporte.
- Exportación CSV con codificación UTF-8 BOM y nombres descriptivos.

### 3.7 Gestión de Usuarios (Admin)
- Crear/editar usuarios con roles `admin`, `supervisor`, `operario`.
- Resetear contraseña y activar/desactivar usuarios.
- Campo `ultimo_acceso` se actualiza en cada login del tenant.

### 3.8 Configuración del Tenant (Admin)
- **Pestañas:** puntos (conversión, vencimiento), datos de contacto, eventos WhatsApp.
- **Conversión de Puntos:** define cuántos pesos equivalen a 1 punto (default: 100).
- **Vencimiento de Puntos:** días antes de que los puntos caduquen (default: 180).
- **Reglas de Acumulación:**
  - Switch "Excluir e-Facturas": cuando está activo, las e-Facturas (CFE 111/112/113) se registran pero no acumulan puntos (`estado=omitido`, `motivo=excluir_efacturas`).
  - Las notas de crédito (CFE 102 y 112) siempre restan puntos (puntos negativos), independientemente de las reglas.
- **Moneda y Conversión:**
  - Moneda base (ej. UYU, ARS).
  - Tasa USD → base (ej. 1 USD = 41 UYU).
  - Política para monedas desconocidas: "Omitir (no acumula)" o "Procesar sin convertir".
- **Eventos de WhatsApp:** 4 flags independientes (puntos canjeados, puntos por vencer, promociones activas, bienvenida a nuevos clientes).
- **Datos de Contacto:** nombre comercial, teléfono, dirección, email. Estos datos aparecen en el portal público y se usan en notificaciones WhatsApp.
- Valores se guardan en tabla `configuracion` (SQLite). El portal público usa los datos de contacto configurados.

---

## 4. Roles y Permisos (Tenant)

| Acción / Módulo | Admin | Supervisor | Operario |
|-----------------|:-----:|:----------:|:--------:|
| Dashboard | ✅ | ✅ | ✅ |
| Listar clientes / ver detalle | ✅ | ✅ | ✅ |
| Editar cliente | ✅ | ✅ | ❌ |
| Canjear puntos | ✅ | ✅ | ❌ |
| Ver reportes | ✅ | ✅ | ✅ |
| Exportar CSV | ✅ | ✅ | ❌ |
| Promociones | ✅ | ❌ | ❌ |
| Usuarios | ✅ | ❌ | ❌ |
| Configuración | ✅ | ❌ | ❌ |

---

## 5. Buenas Prácticas Operativas
1. Revisar dashboard al iniciar la jornada para detectar anomalías.
2. Validar datos del cliente antes de canjear (reducción de errores).
3. Registrar promociones con anticipación y verificar su prioridad.
4. Exportar reportes periódicos como respaldo.
5. Mantener datos de contacto actualizados para portal público y campañas.
6. Informar inmediatamente al SuperAdmin si se requiere suspender un tenant o regenerar API Key.

---

## 6. Preguntas Frecuentes
- **¿Dónde veo el último JSON recibido por webhook?** Panel del tenant → menú Webhook Inbox (últimos eventos con payload formateado, estado y puntos).
- **¿Qué significan los CfeId?**
  - 101: e-Ticket (suma puntos)
  - 102: Nota de crédito e-Ticket (resta puntos)
  - 111: e-Factura (puede omitirse si está marcado "Excluir e-Facturas")
  - 112: Nota de crédito e-Factura (resta puntos)
  - 113: e-Factura de exportación / otros formatos
- **¿Cómo pruebo el webhook?** Con el emulador: `php scripts/emulador_webhook.php --rut=RUT --api-key=KEY --cfeid=101 --doc-mode=ci --monto=5000 --moneda=UYU`.
- **¿Qué pasa si activo "Excluir e-Facturas"?** Las e-Facturas (111/112/113) se registran pero puntos = 0. Solo acumulan e-Tickets y otros comprobantes.
- **¿Se recalculan puntos si cambio la configuración?** No. La configuración (puntos por peso, tasa USD, reglas) solo afecta a nuevas facturas. Las ya procesadas mantienen sus valores originales.
- **¿Dónde configuro SMTP/WhatsApp?** Solo SuperAdmin (`/superadmin/config`).
- **¿Cómo creo un nuevo comercio?** SuperAdmin → Tenants → “Crear tenant” (genera base SQLite, migraciones y usuarios).

---

## 7. Notificaciones Automáticas

### 7.1 Mensajes WhatsApp a Clientes
El sistema envía mensajes WhatsApp a los clientes según los eventos activados en "Configuración → WhatsApp":

- **Puntos Canjeados**: "Hola {Nombre}, canjeaste {X} puntos en {Comercio}. Tu saldo actual es {Y} puntos. ¡Gracias!"
- **Puntos por Vencer**: "Hola {Nombre}, tienes {X} puntos que vencen el {Fecha} en {Comercio}. ¡Úsalos antes de perderlos!" (se envía cuando hay puntos próximos a vencer).
- **Bienvenida a Nuevos Clientes**: "¡Bienvenido {Nombre}! Ya eres parte del programa de puntos de {Comercio}. Acumula puntos con cada compra." (se envía al procesar la primera factura del cliente).
- **Promociones Activas**: "¡Oferta especial en {Comercio}! {Descripción}. Válida hasta {Fecha}." (se envía al activar una nueva promoción).

Todos los mensajes usan los datos de "Información de Contacto" del tenant y se envían solo si el cliente tiene teléfono registrado.

### 7.2 Email Diario al Comercio
El sistema envía un resumen diario al email de contacto del tenant (a las 8:00 AM) con:
- Facturas procesadas del día anterior.
- Puntos generados y canjeados.
- Nuevos clientes registrados.
- Estado general (clientes activos, puntos en circulación, facturas del mes).
- Alerta de clientes con puntos por vencer en los próximos 7 días.

Este email se genera automáticamente vía cron (`php artisan tenant:send-daily-reports`).

---

## 8. Documentación Complementaria
- `docs/ARQUITECTURA.md`: detalles técnicos, flujos y tablas.
- `MANUAL_DEPLOYMENT.md`: instalación, mantenimiento, backups, configuración de cron.
- `docs/CHECKLIST_TAREAS.md`: checklist de pruebas manuales y validación antes de producción.

---

**© 2025 Sistema de Puntos** — Manual resumido para el uso operativo del SuperAdmin y de los comercios (tenants). 
