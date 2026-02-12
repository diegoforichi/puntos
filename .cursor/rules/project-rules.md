# Reglas del Proyecto - Sistema de Puntos (Multi-Tenant)

## 🎯 Principios

- **Simplicidad primero**: soluciones directas, sin sobre-ingeniería.
- **Hosting compartido manda**: nada que dependa de Node/Composer en el servidor.
- **No bloquear flujos**: WhatsApp/Email siempre por cola en operaciones masivas.

## 🚨 Restricciones del entorno

- Cron mínimo: normalmente **cada 15 minutos**.
- Worker: `queue:work` suele correr por cron con `--stop-when-empty`.
- **Subir `vendor/`** (no ejecutar composer en servidor).
- **Compilar assets localmente** (no ejecutar npm en servidor).

## 🗄️ Base de datos (real)

- **MySQL (global)**: configuración, tenants, usuarios globales, cola `jobs`.
- **SQLite (tenant)**: datos operativos del comercio (clientes, facturas, canjes, promociones, logs).

> Nota: SQLite se usa en producción por tenant en este proyecto (con sus límites de concurrencia). Si aparece un caso de alta concurrencia, se evalúa migración a MySQL por tenant o single-db con `tenant_id`.

## 📌 Reglas críticas de negocio

- **WhatsApp “dosificado”**: no enviar en loops sin delay ni “sync”.
- **Campañas**: los IDs de campañas **no son globales** entre tenants. Los Jobs deben recibir `tenantId`.
- **Inserts masivos en SQLite**: usar **chunks** (evitar `too many SQL variables`).

