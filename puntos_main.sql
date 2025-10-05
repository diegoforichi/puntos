-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-10-2025 a las 01:11:31
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `puntos_main`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `accion` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_09_29_210012_create_tenants_table', 1),
(6, '2025_09_29_210020_create_system_config_table', 1),
(7, '2025_09_29_210027_create_webhook_inbox_global_table', 1),
(8, '2025_09_30_211449_add_role_status_to_users_table', 2),
(9, '2025_09_30_211604_create_admin_logs_table', 2),
(10, '2025_09_30_213241_add_gestion_fields_to_tenants_table', 2),
(11, '2025_10_01_125244_add_tracking_columns_to_tenants_table', 3),
(12, '2025_10_02_033500_alter_webhook_inbox_global_add_columns', 4),
(13, '2025_10_02_160500_update_webhook_inbox_global_estado_enum', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_config`
--

CREATE TABLE `system_config` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL COMMENT 'Clave de configuración',
  `value` text DEFAULT NULL COMMENT 'Valor en JSON',
  `description` varchar(500) DEFAULT NULL COMMENT 'Descripción del parámetro',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `system_config`
--

INSERT INTO `system_config` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'whatsapp', 'eyJpdiI6IjJjTDJjbHN5bTFuUlJTVnFERzFmZXc9PSIsInZhbHVlIjoiZ2NzYWxoVVA2MUNZcEFSZzh6NGx4Y3VnZHlQOURzVzVqOHdSaG1WN2tJSFBTbHFheGRrK04yU0s1b0lwd3FyK2tkQ1ZZdm9lRzRuRGdVSCs1VURrMExKR2lhaTVhMnlZdkphaDhxdUFhNUJybEljbWgyNW5iRVR1VW1JWXlMaHdqZmk2WkJMN0Z4NDJqZFk2R3Q1QmR2YkdJRlg4LzMvaVd0WlJsL0ZRZmhwTnpOcVJiRVVINmZPbzN4MEsvZzMrSWhZRHQ0OHNiSHVscUVqQXZMVXJRQT09IiwibWFjIjoiNjY0ZDhkNjllNThlYTAzNTg5MmQ0MTUxYmY0Y2NlOGU0ZDc1MjEzOTY3NjhlMzNiMGRjNjc2NjQzNjdkYmFjMiIsInRhZyI6IiJ9', 'Configuración del servicio WhatsApp', '2025-09-30 00:01:45', '2025-10-02 23:19:44'),
(2, 'email', 'eyJpdiI6ImRJVjJGU1d5L1pUQ2NNZmVkR0g5YlE9PSIsInZhbHVlIjoiU2RFNldXdUZIWEZwc1hpN2FRQlNzczR5YWV6VjlpdERjTkQ1K3grVVlUZUNoN3VvZ0FJQWYzdXEyL2RtaVhJdUYvS3dUWERWNnpuVFNoSzhUaWk1UWUydFpLU3dnNmtja0I5ZGVpcFkyNkdkK0ZxQjNiMUEwdnp4VDM1THUvVmRoanFnWFRMNGt2eUlXaE9FRXQ1Vk5FMkNCQ2ordEpQMXdUMEhpK2VUc3djOFNWNEg4WVhUZXU2MkpLZ2tDNzFwOUEyR3N4dHB0NFZTdFY4dWsreEIvZ0pmdHhPMm1XWm1IQU5wWlNOVGt5OHBtYVA4c1UxSVdsWWZ2WFlFaDdQUFZWWjhVMTdTZ0liMHVOdlU2Wk9TOVY2Vmk2K0MyMkU1bUxpRnhIOGpNR1lBdVE0ZmM2OUZFSnRTd3diaUU0K2IiLCJtYWMiOiIxZDc2ZmQ0OTdiMzEyYmU3MzNhMDVjOTk3NzY4ZDAxYTEwODY0MzM4YTkzMDQwYjg3ZjNmMzFiYjRkMzYyMGMyIiwidGFnIjoiIn0=', 'Configuración del servicio Email', '2025-09-30 00:01:45', '2025-10-02 22:50:43'),
(3, 'retencion_datos', '{\"a\\u00f1os\":1,\"tablas\":[\"puntos_canjeados\",\"puntos_vencidos\",\"actividades\",\"whatsapp_logs\",\"facturas\"]}', 'Política de retención de datos históricos', '2025-09-30 00:01:45', '2025-09-30 00:01:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tenants`
--

CREATE TABLE `tenants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rut` varchar(20) NOT NULL COMMENT 'RUT del comercio/tenant',
  `nombre_comercial` varchar(255) NOT NULL COMMENT 'Nombre comercial del negocio',
  `api_key` varchar(100) NOT NULL COMMENT 'API Key para webhook',
  `estado` enum('activo','suspendido','eliminado') NOT NULL DEFAULT 'activo',
  `sqlite_path` varchar(500) NOT NULL COMMENT 'Ruta al archivo SQLite del tenant',
  `nombre_contacto` varchar(255) DEFAULT NULL,
  `email_contacto` varchar(255) DEFAULT NULL,
  `telefono_contacto` varchar(50) DEFAULT NULL,
  `direccion_contacto` varchar(500) DEFAULT NULL,
  `formato_factura` varchar(50) NOT NULL DEFAULT 'efactura' COMMENT 'Adaptador a usar: efactura, factupronto, etc',
  `ultimo_webhook` timestamp NULL DEFAULT NULL,
  `ultima_migracion` timestamp NULL DEFAULT NULL,
  `ultima_respaldo` timestamp NULL DEFAULT NULL,
  `facturas_recibidas` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `puntos_generados_total` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha de eliminación lógica'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tenants`
--

INSERT INTO `tenants` (`id`, `rut`, `nombre_comercial`, `api_key`, `estado`, `sqlite_path`, `nombre_contacto`, `email_contacto`, `telefono_contacto`, `direccion_contacto`, `formato_factura`, `ultimo_webhook`, `ultima_migracion`, `ultima_respaldo`, `facturas_recibidas`, `puntos_generados_total`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, '050090710017', 'PuntoCom', 'tk_Pq48U7WvTR5dTJYlGXhMiWQ94xBib8RJq4lpG2hz', 'eliminado', 'C:\\xampp\\htdocs\\puntos\\app\\storage\\tenants_archive/050090710017_20251002_120113.sqlite', NULL, 'diegoforichi@gmail.com', '098574709', 'Durazno', 'efactura', NULL, NULL, '2025-10-02 15:01:13', 0, 0, '2025-10-01 03:02:37', '2025-10-02 15:01:13', '2025-10-02 15:01:13'),
(3, '010405023321', 'Acme', 'tk_oVS4NBKHk3IqnajA1wNkCh72S23EnnyW7MZsQUdU', 'eliminado', 'C:\\xampp\\htdocs\\puntos\\app\\storage\\tenants_archive/010405023321_20251002_124553.sqlite', NULL, 'vetprogresas@gmail.com', '897987987', 'durazno', 'efactura', NULL, NULL, '2025-10-02 15:45:53', 0, 0, '2025-10-01 14:03:20', '2025-10-02 15:45:53', '2025-10-02 15:45:53'),
(4, '0104253328', 'Acmes 2', 'tk_OunpeBNRL4wVJ86vurbHSefBsvecA3cJMUfcaCke', 'eliminado', 'C:\\xampp\\htdocs\\puntos\\app\\storage\\tenants_archive/0104253328_20251002_124531.sqlite', NULL, 'diego@gmail.com', '897987987', '987987897', 'efactura', NULL, '2025-10-02 04:59:18', '2025-10-02 15:45:31', 0, 0, '2025-10-01 14:31:23', '2025-10-02 15:45:31', '2025-10-02 15:45:31'),
(5, 'Sorensens', 'sorens', 'tk_Q2fN8cr1kJ4DZ7sOimc5TUKsrBcOQZBZDlCr41Py', 'eliminado', 'C:\\xampp\\htdocs\\puntos\\app\\storage\\tenants_archive/Sorensens_20251002_124521.sqlite', NULL, 'vetprogres2121a@gmail.com', '123123123', '123123123', 'efactura', NULL, '2025-10-02 04:59:13', '2025-10-02 15:45:21', 0, 0, '2025-10-01 15:03:05', '2025-10-02 15:45:21', '2025-10-02 15:45:21'),
(6, 'tractores', 'tracs', 'tk_55kdmBQDI6c6a6FfFnuYHWehFb5dr52xSpUFKktQ', 'eliminado', 'C:\\xampp\\htdocs\\puntos\\app\\storage\\tenants_archive/tractores_20251002_124510.sqlite', NULL, 'vetprogresa@gmail.coms', '123123123123', '123123123123', 'efactura', NULL, '2025-10-01 16:36:20', '2025-10-02 15:45:10', 0, 0, '2025-10-01 15:03:48', '2025-10-02 15:45:10', '2025-10-02 15:45:10'),
(7, '1524560000', 'tracsers', 'tk_aIU5KnTnOmXP3elCW5J7CYsHbW2JlkvCdN0v0oHz', 'eliminado', 'C:\\xampp\\htdocs\\puntos\\app\\storage\\tenants_archive/1524560000_20251002_120050.sqlite', NULL, 'vetprogresa@gmail.coms', '123123123123', '123123123123', 'efactura', NULL, '2025-10-01 16:36:17', '2025-10-02 15:00:50', 0, 0, '2025-10-01 15:05:14', '2025-10-02 15:00:50', '2025-10-02 15:00:50'),
(8, '05478896335', 'Opert', 'tk_b5fkfLHbeqtbwCINTXoez8W3VEXlkEAYMQUYDlEv', 'eliminado', 'C:\\xampp\\htdocs\\puntos\\app\\storage\\tenants_archive/05478896335_20251002_124453.sqlite', NULL, 'vetprogresa22@gmail.coms', '987987897', '9879879 f33', 'efactura', NULL, '2025-10-02 05:45:13', '2025-10-02 15:44:53', 0, 0, '2025-10-01 16:25:01', '2025-10-02 15:44:53', '2025-10-02 15:44:53'),
(12, '000000000010', 'Tapers', 'tk_WaTIMbYFW042ReEsGnky3YCazdNnsAXOyvGNyD5T', 'eliminado', 'C:\\xampp\\htdocs\\puntos\\app\\storage\\tenants_archive/000000000010_20251002_124431.sqlite', NULL, 'vetprogr222esa@gmail.coms', '654987654', '9876546 33', 'efactura', NULL, '2025-10-02 14:58:53', '2025-10-02 15:44:31', 0, 0, '2025-10-02 14:30:13', '2025-10-02 15:44:31', '2025-10-02 15:44:31'),
(13, '00000000000016', 'Puntos', 'tk_CcWuMNQjuaAxFTXMMcgciFH6isYKh5sLoh1W0QxH', 'eliminado', 'C:\\xampp\\htdocs\\puntos\\app\\storage\\tenants_archive/00000000000016_20251002_131031.sqlite', NULL, 'Puntos@gmail.com', '098748089', 'Durazno 232', 'efactura', '2025-10-02 16:07:44', '2025-10-02 15:48:36', '2025-10-02 16:10:31', 1, 60, '2025-10-02 15:48:35', '2025-10-02 16:10:31', '2025-10-02 16:10:31'),
(15, '000000000016', 'Puntos', 'tk_l7XyLbs4HeYrqqMfEYO0rJxiNsC2NI291S8Axwju', 'activo', 'C:\\xampp\\htdocs\\puntos\\app\\storage\\tenants/000000000016.sqlite', NULL, 'vetpro3gresa@gmail.coms', '654321897', '897 22', 'efactura', '2025-10-02 19:30:11', '2025-10-02 16:21:02', NULL, 9, 419, '2025-10-02 16:21:01', '2025-10-02 19:30:11', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'superadmin',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'SuperAdmin', 'superadmin@puntos.local', NULL, '$2y$10$LAJhfh6LbOhlA22ueM3JSeJMiAVkp4OF9ckHGz94p1wWzlNPNMffe', 'superadmin', 'active', NULL, '2025-10-01 00:56:09', '2025-10-01 00:56:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `webhook_inbox_global`
--

CREATE TABLE `webhook_inbox_global` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_rut` varchar(20) DEFAULT NULL COMMENT 'RUT del tenant',
  `estado` enum('pendiente','procesado','error','omitido') NOT NULL DEFAULT 'pendiente',
  `origen` varchar(100) DEFAULT NULL COMMENT 'Origen/adaptador usado',
  `http_status` int(11) DEFAULT NULL COMMENT 'Código HTTP de respuesta',
  `mensaje_error` text DEFAULT NULL COMMENT 'Mensaje de error si falla',
  `payload_json` text DEFAULT NULL COMMENT 'JSON recibido (primeros 5000 chars)',
  `cfe_id` int(10) UNSIGNED DEFAULT NULL,
  `documento_cliente` varchar(50) DEFAULT NULL,
  `puntos_generados` decimal(10,2) DEFAULT NULL,
  `motivo_no_acumulo` varchar(255) DEFAULT NULL,
  `procesado_en` timestamp NULL DEFAULT NULL COMMENT 'Fecha de procesamiento exitoso',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `webhook_inbox_global`
--

INSERT INTO `webhook_inbox_global` (`id`, `tenant_rut`, `estado`, `origen`, `http_status`, `mensaje_error`, `payload_json`, `cfe_id`, `documento_cliente`, `puntos_generados`, `motivo_no_acumulo`, `procesado_en`, `created_at`, `updated_at`) VALUES
(16, '00000000000016', 'error', 'efactura', 500, 'SQLSTATE[01000]: Warning: 1265 Data truncated for column \'estado\' at row 1 (Connection: mysql, SQL: insert into `webhook_inbox_global` (`tenant_rut`, `estado`, `origen`, `http_status`, `mensaje_error`, `payload_json`, `cfe_id`, `documento_cliente`, `puntos_generados`, `motivo_no_acumulo`, `procesado_en`, `created_at`, `updated_at`) values (00000000000016, omitido, efactura, 200, moneda_sin_tasa, {\n    \"Numero\": \"F308-80330\",\n    \"FechaEmision\": \"2025-10-02T15:07:17-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"1819732\",\n        \"Nombre\": \"Mar\\u00eda Garc\\u00eda\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5691864943\",\n        \"Email\": \"cliente@ejemplo.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Producto Est\\u00e1ndar\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5950,\n            \"MontoItem\": 5950\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"CLP\",\n        \"MntNeto\": 5000,\n        \"TasaIVA\": 19,\n        \"IVA\": 950,\n        \"TotMntTotal\": 5950\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 49737,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 49737,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 101\n}, 101, 1819732, 0, moneda_sin_tasa, 2025-10-02 13:07:17, 2025-10-02 13:07:17, 2025-10-02 13:07:17))', '{\n    \"Numero\": \"F308-80330\",\n    \"FechaEmision\": \"2025-10-02T15:07:17-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"1819732\",\n        \"Nombre\": \"Mar\\u00eda Garc\\u00eda\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5691864943\",\n        \"Email\": \"cliente@ejemplo.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Producto Est\\u00e1ndar\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5950,\n            \"MontoItem\": 5950\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"CLP\",\n        \"MntNeto\": 5000,\n        \"TasaIVA\": 19,\n        \"IVA\": 950,\n        \"TotMntTotal\": 5950\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 49737,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 49737,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 101\n}', 101, '1819732', NULL, NULL, '2025-10-02 16:07:17', '2025-10-02 16:07:17', '2025-10-02 16:07:17'),
(17, '00000000000016', 'procesado', 'efactura', 200, NULL, '{\n    \"Numero\": \"F451-43732\",\n    \"FechaEmision\": \"2025-10-02T15:07:44-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"9695098\",\n        \"Nombre\": \"Carlos Mart\\u00ednez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5696755270\",\n        \"Email\": \"carlosmart\\u00ednez@test.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Producto Est\\u00e1ndar\",\n            \"QtyItem\": 2,\n            \"PrcItem\": 2975,\n            \"MontoItem\": 5950\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"UYU\",\n        \"MntNeto\": 5000,\n        \"TasaIVA\": 19,\n        \"IVA\": 950,\n        \"TotMntTotal\": 5950\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 90962,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 90962,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 101\n}', 101, '9695098', 59.50, NULL, '2025-10-02 16:07:44', '2025-10-02 16:07:44', '2025-10-02 16:07:44'),
(18, '000000000016', 'procesado', 'efactura', 200, NULL, '{\n    \"Numero\": \"F502-72388\",\n    \"FechaEmision\": \"2025-10-02T15:27:00-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"3434702\",\n        \"Nombre\": \"Mar\\u00eda Garc\\u00eda\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5696468816\",\n        \"Email\": \"mar\\u00edagarc\\u00eda@test.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Producto de Prueba\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5950,\n            \"MontoItem\": 5950\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"UYU\",\n        \"MntNeto\": 5000,\n        \"TasaIVA\": 19,\n        \"IVA\": 950,\n        \"TotMntTotal\": 5950\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 59496,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 59496,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 101\n}', 101, '3434702', 59.50, NULL, '2025-10-02 16:27:01', '2025-10-02 16:27:01', '2025-10-02 16:27:01'),
(19, '000000000016', 'procesado', 'efactura', 200, NULL, '{\n    \"Numero\": \"F196-01800\",\n    \"FechaEmision\": \"2025-10-02T15:27:32-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"40770075-9\",\n        \"Nombre\": \"Luc\\u00eda S\\u00e1nchez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5694869554\",\n        \"Email\": \"luc\\u00edas\\u00e1nchez@test.com\",\n        \"CodigoTipoDocumento\": \"RUT\",\n        \"TipoDocumento\": \"Rol \\u00danico Tributario\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Art\\u00edculo Variado\",\n            \"QtyItem\": 2,\n            \"PrcItem\": 2975,\n            \"MontoItem\": 5950\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"UYU\",\n        \"MntNeto\": 5000,\n        \"TasaIVA\": 19,\n        \"IVA\": 950,\n        \"TotMntTotal\": 5950\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 45861,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 45861,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 111\n}', 111, '40770075-9', 59.50, NULL, '2025-10-02 16:27:32', '2025-10-02 16:27:32', '2025-10-02 16:27:32'),
(20, '000000000016', 'procesado', 'efactura', 200, NULL, '{\n    \"Numero\": \"F995-17650\",\n    \"FechaEmision\": \"2025-10-02T15:27:55-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"7013297\",\n        \"Nombre\": \"Pedro Rodr\\u00edguez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5697261326\",\n        \"Email\": \"cliente@ejemplo.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Bien de Consumo\",\n            \"QtyItem\": 3,\n            \"PrcItem\": 1190,\n            \"MontoItem\": 3570\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"UYU\",\n        \"MntNeto\": 3000,\n        \"TasaIVA\": 19,\n        \"IVA\": 570,\n        \"TotMntTotal\": 3570\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 61334,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 61334,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 112\n}', 112, '7013297', -35.70, NULL, '2025-10-02 16:27:56', '2025-10-02 16:27:56', '2025-10-02 16:27:56'),
(21, '000000000016', 'procesado', 'efactura', 200, NULL, '{\n    \"Numero\": \"F649-69830\",\n    \"FechaEmision\": \"2025-10-02T15:28:16-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"5382149\",\n        \"Nombre\": \"Pedro Garc\\u00eda\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5691353278\",\n        \"Email\": \"cliente@ejemplo.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Bien de Consumo\",\n            \"QtyItem\": 2,\n            \"PrcItem\": 60,\n            \"MontoItem\": 119\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"USD\",\n        \"MntNeto\": 100,\n        \"TasaIVA\": 19,\n        \"IVA\": 19,\n        \"TotMntTotal\": 119\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 55592,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 55592,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 101\n}', 101, '5382149', 47.60, NULL, '2025-10-02 16:28:16', '2025-10-02 16:28:16', '2025-10-02 16:28:16'),
(22, '000000000016', 'error', 'efactura', 500, 'SQLSTATE[01000]: Warning: 1265 Data truncated for column \'estado\' at row 1 (Connection: mysql, SQL: insert into `webhook_inbox_global` (`tenant_rut`, `estado`, `origen`, `http_status`, `mensaje_error`, `payload_json`, `cfe_id`, `documento_cliente`, `puntos_generados`, `motivo_no_acumulo`, `procesado_en`, `created_at`, `updated_at`) values (000000000016, omitido, efactura, 200, moneda_sin_tasa, {\n    \"Numero\": \"F509-99688\",\n    \"FechaEmision\": \"2025-10-02T15:29:19-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"8685408\",\n        \"Nombre\": \"Mar\\u00eda Mart\\u00ednez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5691612759\",\n        \"Email\": \"mar\\u00edamart\\u00ednez@test.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Bien de Consumo\",\n            \"QtyItem\": 2,\n            \"PrcItem\": 60,\n            \"MontoItem\": 119\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"BRL\",\n        \"MntNeto\": 100,\n        \"TasaIVA\": 19,\n        \"IVA\": 19,\n        \"TotMntTotal\": 119\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 77327,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 77327,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 101\n}, 101, 8685408, 0, moneda_sin_tasa, 2025-10-02 13:29:20, 2025-10-02 13:29:20, 2025-10-02 13:29:20))', '{\n    \"Numero\": \"F509-99688\",\n    \"FechaEmision\": \"2025-10-02T15:29:19-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"8685408\",\n        \"Nombre\": \"Mar\\u00eda Mart\\u00ednez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5691612759\",\n        \"Email\": \"mar\\u00edamart\\u00ednez@test.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Bien de Consumo\",\n            \"QtyItem\": 2,\n            \"PrcItem\": 60,\n            \"MontoItem\": 119\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"BRL\",\n        \"MntNeto\": 100,\n        \"TasaIVA\": 19,\n        \"IVA\": 19,\n        \"TotMntTotal\": 119\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 77327,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 77327,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 101\n}', 101, '8685408', NULL, NULL, '2025-10-02 16:29:20', '2025-10-02 16:29:20', '2025-10-02 16:29:20'),
(23, '000000000016', 'error', 'efactura', 500, 'SQLSTATE[01000]: Warning: 1265 Data truncated for column \'estado\' at row 1 (Connection: mysql, SQL: insert into `webhook_inbox_global` (`tenant_rut`, `estado`, `origen`, `http_status`, `mensaje_error`, `payload_json`, `cfe_id`, `documento_cliente`, `puntos_generados`, `motivo_no_acumulo`, `procesado_en`, `created_at`, `updated_at`) values (000000000016, omitido, efactura, 200, moneda_sin_tasa, {\n    \"Numero\": \"F068-60599\",\n    \"FechaEmision\": \"2025-10-02T15:29:56-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"9461615\",\n        \"Nombre\": \"Pedro L\\u00f3pez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5698604876\",\n        \"Email\": \"pedrol\\u00f3pez@test.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 3,\n            \"PrcItem\": 40,\n            \"MontoItem\": 119\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"BRL\",\n        \"MntNeto\": 100,\n        \"TasaIVA\": 19,\n        \"IVA\": 19,\n        \"TotMntTotal\": 119\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 43537,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 43537,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 101\n}, 101, 9461615, 0, moneda_sin_tasa, 2025-10-02 13:29:57, 2025-10-02 13:29:57, 2025-10-02 13:29:57))', '{\n    \"Numero\": \"F068-60599\",\n    \"FechaEmision\": \"2025-10-02T15:29:56-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"9461615\",\n        \"Nombre\": \"Pedro L\\u00f3pez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5698604876\",\n        \"Email\": \"pedrol\\u00f3pez@test.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 3,\n            \"PrcItem\": 40,\n            \"MontoItem\": 119\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"BRL\",\n        \"MntNeto\": 100,\n        \"TasaIVA\": 19,\n        \"IVA\": 19,\n        \"TotMntTotal\": 119\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 43537,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 43537,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 101\n}', 101, '9461615', NULL, NULL, '2025-10-02 16:29:57', '2025-10-02 16:29:57', '2025-10-02 16:29:57'),
(24, '000000000016', 'procesado', 'efactura', 200, NULL, '{\n    \"Numero\": \"F031-95558\",\n    \"FechaEmision\": \"2025-10-02T17:13:52-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"6952352\",\n        \"Nombre\": \"Mar\\u00eda P\\u00e9rez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5691822710\",\n        \"Email\": \"mar\\u00edap\\u00e9rez@test.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Art\\u00edculo Variado\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5950,\n            \"MontoItem\": 5950\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"UYU\",\n        \"MntNeto\": 5000,\n        \"TasaIVA\": 19,\n        \"IVA\": 950,\n        \"TotMntTotal\": 5950\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 74346,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 74346,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 101\n}', 101, '6952352', 119.00, NULL, '2025-10-02 18:13:52', '2025-10-02 18:13:52', '2025-10-02 18:13:52'),
(25, '000000000016', 'procesado', 'efactura', 200, NULL, '{\n    \"Numero\": \"F083-04611\",\n    \"FechaEmision\": \"2025-10-02T17:14:06-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"90248677-1\",\n        \"Nombre\": \"Juan L\\u00f3pez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5696371994\",\n        \"Email\": \"juanl\\u00f3pez@test.com\",\n        \"CodigoTipoDocumento\": \"RUT\",\n        \"TipoDocumento\": \"Rol \\u00danico Tributario\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Art\\u00edculo Variado\",\n            \"QtyItem\": 3,\n            \"PrcItem\": 1983,\n            \"MontoItem\": 5950\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"UYU\",\n        \"MntNeto\": 5000,\n        \"TasaIVA\": 19,\n        \"IVA\": 950,\n        \"TotMntTotal\": 5950\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 15233,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 15233,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 111\n}', 111, '90248677-1', 119.00, NULL, '2025-10-02 18:14:06', '2025-10-02 18:14:06', '2025-10-02 18:14:06'),
(26, '000000000016', 'procesado', 'efactura', 200, NULL, '{\n    \"Numero\": \"F163-77887\",\n    \"FechaEmision\": \"2025-10-02T18:03:16-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"4070632\",\n        \"Nombre\": \"Sof\\u00eda P\\u00e9rez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5693453270\",\n        \"Email\": \"cliente@ejemplo.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Mercanc\\u00eda General\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 119,\n            \"MontoItem\": 119\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"USD\",\n        \"MntNeto\": 100,\n        \"TasaIVA\": 19,\n        \"IVA\": 19,\n        \"TotMntTotal\": 119\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 78864,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 78864,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 101\n}', 101, '4070632', 48.79, NULL, '2025-10-02 19:03:16', '2025-10-02 19:03:16', '2025-10-02 19:03:16'),
(27, '000000000016', 'error', 'efactura', 500, 'SQLSTATE[01000]: Warning: 1265 Data truncated for column \'estado\' at row 1 (Connection: mysql, SQL: insert into `webhook_inbox_global` (`tenant_rut`, `estado`, `origen`, `http_status`, `mensaje_error`, `payload_json`, `cfe_id`, `documento_cliente`, `puntos_generados`, `motivo_no_acumulo`, `procesado_en`, `created_at`, `updated_at`) values (000000000016, omitido, efactura, 200, excluir_efacturas, {\n    \"Numero\": \"F713-29039\",\n    \"FechaEmision\": \"2025-10-02T18:03:25-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"3656914-K\",\n        \"Nombre\": \"Sof\\u00eda Fern\\u00e1ndez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5697743105\",\n        \"Email\": \"sof\\u00edafern\\u00e1ndez@test.com\",\n        \"CodigoTipoDocumento\": \"RUT\",\n        \"TipoDocumento\": \"Rol \\u00danico Tributario\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Bien de Consumo\",\n            \"QtyItem\": 3,\n            \"PrcItem\": 40,\n            \"MontoItem\": 119\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"USD\",\n        \"MntNeto\": 100,\n        \"TasaIVA\": 19,\n        \"IVA\": 19,\n        \"TotMntTotal\": 119\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 97313,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 97313,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 111\n}, 111, 3656914-K, 0, excluir_efacturas, 2025-10-02 16:03:25, 2025-10-02 16:03:25, 2025-10-02 16:03:25))', '{\n    \"Numero\": \"F713-29039\",\n    \"FechaEmision\": \"2025-10-02T18:03:25-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"3656914-K\",\n        \"Nombre\": \"Sof\\u00eda Fern\\u00e1ndez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5697743105\",\n        \"Email\": \"sof\\u00edafern\\u00e1ndez@test.com\",\n        \"CodigoTipoDocumento\": \"RUT\",\n        \"TipoDocumento\": \"Rol \\u00danico Tributario\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Bien de Consumo\",\n            \"QtyItem\": 3,\n            \"PrcItem\": 40,\n            \"MontoItem\": 119\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"USD\",\n        \"MntNeto\": 100,\n        \"TasaIVA\": 19,\n        \"IVA\": 19,\n        \"TotMntTotal\": 119\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 97313,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 97313,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 111\n}', 111, '3656914-K', NULL, NULL, '2025-10-02 19:03:25', '2025-10-02 19:03:25', '2025-10-02 19:03:25'),
(28, '000000000016', 'omitido', 'efactura', 200, 'excluir_efacturas', '{\n    \"Numero\": \"F562-81227\",\n    \"FechaEmision\": \"2025-10-02T18:30:04-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"4545002-3\",\n        \"Nombre\": \"Mar\\u00eda S\\u00e1nchez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5695634246\",\n        \"Email\": \"mar\\u00edas\\u00e1nchez@test.com\",\n        \"CodigoTipoDocumento\": \"RUT\",\n        \"TipoDocumento\": \"Rol \\u00danico Tributario\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Art\\u00edculo Variado\",\n            \"QtyItem\": 3,\n            \"PrcItem\": 40,\n            \"MontoItem\": 119\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"USD\",\n        \"MntNeto\": 100,\n        \"TasaIVA\": 19,\n        \"IVA\": 19,\n        \"TotMntTotal\": 119\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 68995,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 68995,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 111\n}', 111, '4545002-3', 0.00, 'excluir_efacturas', '2025-10-02 19:30:04', '2025-10-02 19:30:04', '2025-10-02 19:30:04'),
(29, '000000000016', 'omitido', 'efactura', 200, 'moneda_sin_tasa', '{\n    \"Numero\": \"F361-30067\",\n    \"FechaEmision\": \"2025-10-02T18:30:11-03:00\",\n    \"TipoDocumento\": \"FACTURA\",\n    \"Emisor\": {\n        \"RUT\": \"000000000016\",\n        \"RazonSocial\": \"Comercio Demo S.A.\",\n        \"Direccion\": \"Av. Principal 123, Santiago\",\n        \"Comuna\": \"Santiago\",\n        \"Region\": \"Metropolitana\"\n    },\n    \"Client\": {\n        \"NroDoc\": \"3608439\",\n        \"Nombre\": \"Pedro Fern\\u00e1ndez\",\n        \"Direccion\": \"Calle Falsa 456\",\n        \"Comuna\": \"Providencia\",\n        \"Region\": \"Metropolitana\",\n        \"Telefono\": \"+5692353005\",\n        \"Email\": \"cliente@ejemplo.com\",\n        \"CodigoTipoDocumento\": \"CI\",\n        \"TipoDocumento\": \"C\\u00e9dula de Identidad\"\n    },\n    \"Detalle\": [\n        {\n            \"NroLinDet\": 1,\n            \"NmbItem\": \"Mercanc\\u00eda General\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 119,\n            \"MontoItem\": 119\n        },\n        {\n            \"NroLinDet\": 2,\n            \"NmbItem\": \"Servicio Adicional\",\n            \"QtyItem\": 1,\n            \"PrcItem\": 5000,\n            \"MontoItem\": 5000\n        }\n    ],\n    \"Totales\": {\n        \"TpoMoneda\": \"BRL\",\n        \"MntNeto\": 100,\n        \"TasaIVA\": 19,\n        \"IVA\": 19,\n        \"TotMntTotal\": 119\n    },\n    \"Referencias\": [\n        {\n            \"TpoDocRef\": \"FACTURA\",\n            \"FolioRef\": \"F001-00012344\",\n            \"FchRef\": \"2025-01-10\"\n        }\n    ],\n    \"IdDoc\": {\n        \"TipoDTE\": 33,\n        \"Folio\": 53180,\n        \"FchEmis\": \"2025-01-15\"\n    },\n    \"Encabezado\": {\n        \"IdDoc\": {\n            \"TipoDTE\": 33,\n            \"Folio\": 53180,\n            \"FchEmis\": \"2025-01-15\"\n        },\n        \"Emisor\": {\n            \"RUTEmisor\": \"12345678-9\",\n            \"RznSoc\": \"Comercio Demo S.A.\",\n            \"GiroEmis\": \"Comercio al por menor\",\n            \"Acteco\": \"471100\",\n            \"DirOrigen\": \"Av. Principal 123\",\n            \"CmnaOrigen\": \"Santiago\"\n        },\n        \"Receptor\": {\n            \"RUTRecep\": \"12345678-9\",\n            \"RznSocRecep\": \"Cliente Prueba\",\n            \"DirRecep\": \"Calle Falsa 456\",\n            \"CmnaRecep\": \"Providencia\"\n        }\n    },\n    \"CfeId\": 101\n}', 101, '3608439', 0.00, 'moneda_sin_tasa', '2025-10-02 19:30:11', '2025-10-02 19:30:11', '2025-10-02 19:30:11');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_logs_user_id_foreign` (`user_id`),
  ADD KEY `admin_logs_accion_index` (`accion`),
  ADD KEY `admin_logs_created_at_index` (`created_at`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_config_key_unique` (`key`),
  ADD KEY `system_config_key_index` (`key`);

--
-- Indices de la tabla `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenants_rut_unique` (`rut`),
  ADD UNIQUE KEY `tenants_api_key_unique` (`api_key`),
  ADD KEY `tenants_estado_index` (`estado`),
  ADD KEY `tenants_created_at_index` (`created_at`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indices de la tabla `webhook_inbox_global`
--
ALTER TABLE `webhook_inbox_global`
  ADD PRIMARY KEY (`id`),
  ADD KEY `webhook_inbox_global_tenant_rut_index` (`tenant_rut`),
  ADD KEY `webhook_inbox_global_estado_index` (`estado`),
  ADD KEY `webhook_inbox_global_created_at_index` (`created_at`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `system_config`
--
ALTER TABLE `system_config`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `webhook_inbox_global`
--
ALTER TABLE `webhook_inbox_global`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `webhook_inbox_global`
--
ALTER TABLE `webhook_inbox_global`
  ADD CONSTRAINT `webhook_inbox_global_tenant_rut_foreign` FOREIGN KEY (`tenant_rut`) REFERENCES `tenants` (`rut`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
