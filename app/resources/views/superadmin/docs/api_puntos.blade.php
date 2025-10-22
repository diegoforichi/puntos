@extends('superadmin.layout')

@section('title', 'Documentación API de Puntos')
@section('page-title', 'API de Puntos')
@section('page-subtitle', 'Guía para integradores externos')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body py-4">
        <p class="text-muted">Esta API permite consultar y canjear puntos de clientes directamente desde sistemas externos como software de facturación. Todas las rutas requieren autenticación mediante token por tenant.</p>

        <h5 class="mt-4">Autenticación</h5>
        <ul>
            <li>Header obligatorio: <code>Authorization: Bearer &lt;TOKEN_DEL_TENANT&gt;</code></li>
            <li>El token se genera y se puede regenerar desde el panel de SuperAdmin.</li>
            <li>Las respuestas de error usan códigos HTTP estándar (401, 404, 422, 500).</li>
        </ul>

        <h5 class="mt-4">1. Consultar puntos</h5>
        <div class="bg-light border rounded p-3 mb-3">
            <pre class="mb-0 small">GET https://app.midominio.com/{RUT_TENANT}/api/clientes/{DOCUMENTO}
Authorization: Bearer TOKEN</pre>
        </div>
        <strong>Respuesta 200:</strong>
        <div class="bg-light border rounded p-3 mb-3">
<pre class="mb-0 small">{
  "documento": "56896934",
  "nombre": "Sandra Devitta",
  "puntos_acumulados": 123.45,
  "puntos_formateados": "123,45",
  "ultima_actividad": "2025-10-14 12:34:56"
}</pre>
        </div>
        <p class="text-muted small">404 si el cliente no existe.</p>

        <h5 class="mt-4">2. Canjear puntos</h5>
        <div class="bg-light border rounded p-3 mb-3">
            <pre class="mb-0 small">POST https://app.midominio.com/{RUT_TENANT}/api/clientes/{DOCUMENTO}/canjes
Authorization: Bearer TOKEN
Content-Type: application/json

{
  "puntos_a_canjear": 25.5,
  "descripcion": "Descuento factura F001-123"
}</pre>
        </div>
        <strong>Respuesta 200:</strong>
        <div class="bg-light border rounded p-3 mb-3">
<pre class="mb-0 small">{
  "mensaje": "Canje realizado con éxito",
  "puntos_anteriores": 150.75,
  "puntos_canjeados": 25.5,
  "puntos_nuevos": 125.25,
  "referencia": "Descuento factura F001-123"
}</pre>
        </div>
        <p class="text-muted small">422 si no tiene puntos suficientes, 404 si el cliente no existe.</p>

        <h5 class="mt-4">Buenas prácticas</h5>
        <ul>
            <li>Guardar el token de forma segura. Si se sospecha filtración, regenerarlo desde el panel.</li>
            <li>Registrar en el sistema externo la referencia asociada al canje (factura, pedido, etc.).</li>
            <li>Implementar reintentos ante errores 5xx y registrar respuestas para auditoría.</li>
        </ul>

        <p class="mt-4"><strong>Soporte:</strong> contactar a soporte@puntos.com indicando el RUT del tenant y la hora aproximada del evento.</p>
    </div>
</div>
@endsection

