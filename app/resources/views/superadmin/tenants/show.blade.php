@extends('superadmin.layout')

@section('title', 'Detalle del Tenant')
@section('page-title', 'Detalle del Tenant')
@section('page-subtitle', $tenant->nombre_comercial)

@section('content')
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información General</h5>
                <span class="badge bg-secondary">RUT {{ $tenant->rut }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Nombre Comercial</dt>
                    <dd class="col-sm-7">{{ $tenant->nombre_comercial }}</dd>

                    <dt class="col-sm-5">Estado</dt>
                    <dd class="col-sm-7">
                        <span class="badge bg-{{ $tenant->estado === 'activo' ? 'success' : ($tenant->estado === 'suspendido' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($tenant->estado) }}
                        </span>
                    </dd>

                    <dt class="col-sm-5">API Key</dt>
                    <dd class="col-sm-7 text-truncate" title="{{ $tenant->api_key }}">{{ $tenant->api_key }}</dd>

                    <dt class="col-sm-5">Puntos generados</dt>
                    <dd class="col-sm-7">{{ number_format($tenant->puntos_generados_total) }}</dd>

                    <dt class="col-sm-5">Facturas procesadas</dt>
                    <dd class="col-sm-7">{{ number_format($tenant->facturas_recibidas) }}</dd>

                    <dt class="col-sm-5">Último webhook</dt>
                    <dd class="col-sm-7">{{ $tenant->ultimo_webhook ? $tenant->ultimo_webhook->format('d/m/Y H:i') : '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-hdd-stack me-2"></i>Base de Datos</h5>
                <span class="badge bg-light text-dark">{{ $sizeMB }} MB</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Ruta actual</dt>
                    <dd class="col-sm-7 small text-break">{{ $tenant->getSqlitePath() }}</dd>

                    <dt class="col-sm-5">Última migración</dt>
                    <dd class="col-sm-7">{{ $tenant->ultima_migracion ? $tenant->ultima_migracion->format('d/m/Y H:i') : '—' }}</dd>

                    <dt class="col-sm-5">Último respaldo</dt>
                    <dd class="col-sm-7">
                        @if($backupInfo)
                            {{ \Carbon\Carbon::createFromTimestamp($backupInfo['created_at'])->format('d/m/Y H:i') }}
                            <span class="text-muted">({{ $backupInfo['size_mb'] }} MB)</span>
                        @else
                            —
                        @endif
                    </dd>
                </dl>

                <hr>

                <form action="{{ route('superadmin.tenants.backup', $tenant) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary flex-fill">
                        <i class="bi bi-save2 me-1"></i> Generar Backup
                    </button>
                    <a href="{{ route('superadmin.tenants.download-backup', $tenant) }}" class="btn btn-outline-secondary flex-fill {{ $backupInfo ? '' : 'disabled' }}">
                        <i class="bi bi-download me-1"></i> Descargar último
                    </a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Usuarios Iniciales</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Usuarios base generados automáticamente al crear el tenant.</p>
                <div class="border rounded p-3 bg-light mb-3">
                    <pre class="mb-0 small">
Usuario: admin{{ $tenant->usernameSuffix() }} | Email: admin@puntos.local | Contraseña: admin123 | Rol: admin
Usuario: supervisor{{ $tenant->usernameSuffix() }} | Email: supervisor@puntos.local | Contraseña: supervisor123 | Rol: supervisor
Usuario: operario{{ $tenant->usernameSuffix() }} | Email: operario@puntos.local | Contraseña: operario123 | Rol: operario</pre>
                </div>
                <form action="{{ route('superadmin.tenants.ensure-db', $tenant) }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning w-100">
                        <i class="bi bi-arrow-repeat me-2"></i> Re-ejecutar migraciones del tenant
                    </button>
                </form>
                <form action="{{ route('superadmin.tenants.seed-users', $tenant) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-success w-100">
                        <i class="bi bi-people me-2"></i> Regenerar usuarios iniciales
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-broadcast me-2"></i>Notificaciones Personalizadas</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Permite que el tenant configure sus propias credenciales para WhatsApp y Email.</p>
                <form action="{{ route('superadmin.tenants.notifications', $tenant) }}" method="POST" class="vstack gap-3">
                    @csrf
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="allowCustomWhatsapp" name="allow_custom_whatsapp" {{ $tenant->allow_custom_whatsapp ? 'checked' : '' }}>
                        <label class="form-check-label" for="allowCustomWhatsapp">
                            <strong>WhatsApp propio</strong>
                            <small class="d-block text-muted">El tenant podrá ingresar URL/Token desde su panel.</small>
                        </label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="allowCustomEmail" name="allow_custom_email" {{ $tenant->allow_custom_email ? 'checked' : '' }}>
                        <label class="form-check-label" for="allowCustomEmail">
                            <strong>Email propio (SMTP)</strong>
                            <small class="d-block text-muted">El tenant podrá definir host, puerto y credenciales.</small>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-outline-primary align-self-start">
                        <i class="bi bi-save me-1"></i>Guardar permisos
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-inbox-arrow-down me-2"></i>Últimos Webhooks</h5>
            </div>
            <div class="card-body">
                @if($webhooks->isEmpty())
                    <p class="text-muted">No hay registros aún.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>HTTP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($webhooks as $hook)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($hook->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>{{ ucfirst($hook->estado) }}</td>
                                        <td>{{ $hook->http_status }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted">Mostrar sólo los 10 más recientes.</small>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-puzzle me-2"></i>Integración API de Puntos</h5>
                <span class="badge bg-primary">Nuevo</span>
            </div>
            <div class="card-body">
                @if(!$tenant->api_token)
                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span>Este tenant aún no tiene token de API asignado. Genera uno para habilitar la integración.</span>
                    </div>
                @endif

                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label">Base URL</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $apiBaseUrl }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ $apiBaseUrl }}')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <small class="text-muted">Añade <code>/clientes/{documento}</code> o <code>/clientes/{documento}/canjes</code>.</small>
                    </div>

                    <div class="col-lg-5">
                        <label class="form-label">Token de API</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $tenant->api_token ?? '—' }}" readonly>
                            <button class="btn btn-outline-secondary {{ $tenant->api_token ? '' : 'disabled' }}" type="button" @if($tenant->api_token) onclick="navigator.clipboard.writeText('{{ $tenant->api_token }}')" @endif>
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <small class="text-muted">Usa el header <code>Authorization: Bearer TOKEN</code>.</small>
                        @if($tenant->api_token_last_used_at)
                            <div class="text-muted small mt-1">
                                <i class="bi bi-clock-history me-1"></i>
                                Último uso: {{ $tenant->api_token_last_used_at->timezone('America/Montevideo')->format('d/m/Y H:i') }}
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-3 text-lg-end">
                        <form action="{{ route('superadmin.tenants.regenerate-api-token', $tenant) }}" method="POST" onsubmit="return confirm('Esto invalidará el token actual. Las integraciones deberán actualizarlo. ¿Deseas continuar?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-arrow-repeat me-1"></i> Regenerar token
                            </button>
                        </form>
                    </div>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="bg-light border rounded p-3 h-100">
                            <h6 class="fw-semibold">Consultar puntos</h6>
                            <pre class="small mb-0">GET {{ $apiBaseUrl }}/clientes/{documento}
Authorization: Bearer {{ $tenant->api_token ?? 'TOKEN' }}</pre>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light border rounded p-3 h-100">
                            <h6 class="fw-semibold">Canjear puntos</h6>
                            <pre class="small mb-0">POST {{ $apiBaseUrl }}/clientes/{documento}/canjes
Authorization: Bearer {{ $tenant->api_token ?? 'TOKEN' }}
Content-Type: application/json
{
  "puntos_a_canjear": 50,
  "descripcion": "Descuento en factura 001-123"
}</pre>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('superadmin.docs.api-puntos') }}" class="btn btn-link p-0">
                        <i class="bi bi-file-earmark-text me-1"></i>Ver documentación completa
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-plug me-2"></i>Integración Webhook</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Endpoint</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ rtrim(config('app.url'), '/') }}/api/webhook/ingest" readonly>
                            <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ rtrim(config('app.url'), '/') }}/api/webhook/ingest')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <small class="text-muted">Se ajusta automáticamente según APP_URL.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Authorization Header</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="Bearer {{ $tenant->api_key }}" readonly>
                            <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('Bearer {{ $tenant->api_key }}')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">cURL de ejemplo</label>
                        <div class="bg-light border rounded p-3">
                            <pre class="mb-0 small">curl -X POST "{{ rtrim(config('app.url'), '/') }}/api/webhook/ingest" \
  -H "Authorization: Bearer {{ $tenant->api_key }}" \
  -H "Content-Type: application/json" \
  --data @scripts/hookCfe.json</pre>
                        </div>
                        <small class="text-muted">El payload de referencia está en `scripts/hookCfe.json`. Uso limitado a entornos de test.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-box-arrow-down me-2"></i>Archivar Tenant</h5>
                <span class="badge bg-danger">Acción permanente</span>
            </div>
            <div class="card-body">
                <p class="text-muted">Archivar moverá la base SQLite a un directorio seguro y marcará el tenant como eliminado. Se generará un backup automáticamente.</p>
                <form action="{{ route('superadmin.tenants.archive', $tenant) }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-lg-4">
                        <label class="form-label">Confirmar RUT</label>
                        <input type="text" name="confirm_rut" class="form-control @error('confirm_rut') is-invalid @enderror" placeholder="{{ $tenant->rut }}" required>
                        @error('confirm_rut')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-8 d-flex align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="confirm_terms" id="confirmTerms" required>
                            <label class="form-check-label" for="confirmTerms">
                                Entiendo que el tenant será desactivado y se moverá su base de datos a un archivo de respaldo.
                            </label>
                            @error('confirm_terms')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-between">
                        <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver
                        </a>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Deseas archivar este tenant? Se generará un backup antes de eliminarlo.')">
                            <i class="bi bi-archive me-2"></i> Archivar Tenant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
