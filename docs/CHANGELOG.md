# Changelog

Historial de cambios del proyecto.
Formato basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/).

---

## 2026-02-11 (Deploy Febrero v3 - Mejora Reportes)

### Agregado
- Filtro por cliente (nombre/documento) en reportes de canjes y facturas
- Campo de busqueda en reporte de clientes (nombre, documento, email)
- Filtro por tipo (canjes vs ajustes) en reporte de canjes
- Estadisticas del periodo en reportes de canjes y facturas (totales reales, no solo de la pagina)
- Boton "Limpiar filtros" en todos los reportes
- Nueva vista `/clientes/{id}/canjes` - historial completo de canjes por cliente con paginacion
- Nueva vista `/clientes/{id}/facturas` - historial completo de facturas por cliente con paginacion
- Links "Ver todos" en la ficha del cliente para facturas y canjes
- Filtros de "Ajustes" y "Clientes editados" en reporte de actividades
- Paginacion completa en reporte de actividades

### Corregido
- Actividad de edicion de cliente registraba `cliente_creado` en vez de `cliente_actualizado`
- Texto "500 actividades" residual en vista de actividades (ahora muestra total real)

---

## 2026-02-11 (Hotfix Campañas - multi-tenant + estabilidad)

### Corregido
- **CRITICO:** `EnviarCampanaJob` podia procesar una campaña del tenant incorrecto por colisión de IDs entre SQLite (ahora recibe `tenantId`)
- **CRITICO:** Creación masiva de campañas podía fallar con SQLite `too many SQL variables` (insert de `campana_envios` ahora es en chunks)

### Cambiado
- `EnviarCampanaJob` despacha `ProcesarEnvioCampana` con delay progresivo para evitar ráfagas de jobs en la cola `campanas`
- `ProcesarCampanasProgramadas` ahora despacha `EnviarCampanaJob` pasando el `tenantId`

---

## 2026-02-11 (Deploy Febrero v2 - Dosificacion WhatsApp)

### Corregido
- **CRITICO:** Comando `puntos:notificar-vencimiento` enviaba todos los mensajes de golpe sin delay (causo bloqueo de token Meta con 644 mensajes en 1 hora)
- Bienvenida a nuevos clientes se enviaba de forma sincrona bloqueando el webhook

### Cambiado
- `NotifyExpiringPoints.php` ahora usa Jobs con delay progresivo de 8 seg entre mensajes
- `PuntosService.php` bienvenida ahora usa Job asincrono con delay aleatorio 3-8 seg
- `EnviarNotificacionWhatsApp.php` soporta 4 tipos: CANJE, PROMOCION, VENCIMIENTO, BIENVENIDA

---

## 2026-02-11 (Deploy Febrero v1 - Correcciones)

### Corregido
- **CRITICO:** Notificacion de puntos por vencer enviaba monto incorrecto (suma de facturas en vez de saldo real). Ejemplo: mensaje decia 73.14 pts, saldo real era 7.03 pts
- `AutoconsultaController` consultaba columna `puntos_disponibles` que no existe, cambiado a `puntos_generados`

---

## 2025-11-16

### Agregado
- Endpoint `POST /api/webhook/ingest/cancel` para cancelacion de facturas
- `PuntosService::ajustarSaldoCliente()` limita ajustes negativos (saldo nunca va negativo)
- Panel SuperAdmin muestra endpoint de cancelacion con cURL de ejemplo

---

## 2025-11-04

### Agregado
- Gestion avanzada de campanias: pausar, reanudar, eliminar (soft delete)
- Formulario manual de creacion de clientes con puntos iniciales opcionales
- Placeholders extendidos en mensajes: `{nombre}`, `{puntos}`, `{comercio}`, `{telefono}`, `{email}`, `{documento}`
- Limite de 200 caracteres para mensajes WhatsApp con contador en tiempo real

### Corregido
- Error de tipo en `ConfiguracionController::probarWhatsAppPersonalizado()`
- Removido `@include('partials.alerts')` duplicado en `clientes/create.blade.php`

---

## 2025-10-25

### Agregado
- Modulo de campanias masivas: modelos, vistas, Jobs asincronos
- Comando `campanas:procesar-programadas` para campanias programadas
- Jobs `EnviarCampanaJob` y `ProcesarEnvioCampana` con rate limiting
- Plantilla email para campanias con placeholders

---

## 2025-10-16

### Agregado
- Documentacion completa del proyecto
- Sistema de traducciones `resources/lang/es/`
- Reglas de Cursor en `.cursor/rules/`

### Configurado
- Idioma por defecto: Espanol (es)
- Laravel 10.x
- Optimizado para hosting compartido
