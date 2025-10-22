# API de Puntos

Guía rápida para integradores (ej. software de facturación) que necesitan consultar saldo y registrar canjes directamente contra el sistema de puntos.

## Autenticación
- Header obligatorio: `Authorization: Bearer TOKEN`. Cada tenant tiene su token propio.
- El token se gestiona desde el panel SuperAdmin (detalle del tenant). Si se regenera, el anterior queda inválido al instante.
- Las respuestas de error utilizan códigos HTTP estándar (401, 404, 422, 500).

## Endpoints
Base general (reemplazar `{RUT_TENANT}`): `https://app.midominio.com/{RUT_TENANT}/api`

### 1. Consultar puntos del cliente
`GET /clientes/{documento}`

Ejemplo:
```http
GET https://app.midominio.com/050154840013/api/clientes/56896934
Authorization: Bearer pk_xxxxxx
```

Respuesta 200:
```json
{
  "status": "success",
  "documento": "56896934",
  "nombre": "Sandra Devitta",
  "puntos_acumulados": 123.45,
  "puntos_formateados": "123,45",
  "ultima_actividad": "2025-10-14 12:34:56"
}
```

Errores:
- 404 cliente no encontrado.
- 401 token inválido o faltante.

### 2. Canjear puntos
`POST /clientes/{documento}/canjes`

Body JSON:
```json
{
  "puntos_a_canjear": 25.5,
  "descripcion": "Descuento factura F001-123",
  "referencia": "F001-123"
}
```

Respuesta 200:
```json
{
  "status": "success",
  "mensaje": "Canje realizado con éxito",
  "puntos_anteriores": 150.75,
  "puntos_canjeados": 25.5,
  "puntos_nuevos": 125.25,
  "referencia": "F001-123"
}
```

Errores:
- 404 cliente inexistente.
- 422 puntos insuficientes.
- 401 token inválido.

## Recomendaciones
- Registrar localmente la referencia del canje (factura, pedido, etc.).
- Implementar reintentos con backoff en caso de errores 5xx.
- Proteger el token como credencial sensible. Si se sospecha filtración, regenerarlo desde SuperAdmin.
- Los canjes API quedan marcados con origen `api` y visibles en reportes y auditoría.

## Soporte
Ante dudas contactar a soporte indicando RUT del tenant, fecha/hora y descripción del evento o solicitud.
