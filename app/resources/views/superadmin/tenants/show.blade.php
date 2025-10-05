@extends('superadmin.layout')

@section('title', 'Detalle del Tenant')
@section('page-title', 'Detalle del Tenant')
@section('page-subtitle', $tenant->nombre_comercial)

@section('content')
@if(session('integration_error'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('integration_error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

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
