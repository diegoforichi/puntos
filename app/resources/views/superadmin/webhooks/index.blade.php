@extends('superadmin.layout')

@section('title', 'Webhook Inbox Global')
@section('page-title', 'Webhook Inbox Global')
@section('page-subtitle', 'Historial de peticiones recibidas por todos los tenants')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-inbox me-2"></i>Registro de webhooks</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Tenant</th>
                        <th>Estado</th>
                        <th>Origen</th>
                        <th>HTTP</th>
                        <th>Mensaje</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($webhooks as $hook)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($hook->created_at)->format('d/m/Y H:i') }}</td>
                            <td><code>{{ $hook->tenant_rut }}</code></td>
                            <td>
                                <span class="badge bg-{{ $hook->estado === 'procesado' ? 'success' : ($hook->estado === 'error' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($hook->estado) }}
                                </span>
                            </td>
                            <td>{{ $hook->origen ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ $hook->http_status }}</span></td>
                            <td>{{ Str::limit($hook->mensaje_error ?? 'OK', 60) }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#payload{{ $hook->id }}">
                                    <i class="bi bi-code"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="payload{{ $hook->id }}">
                            <td colspan="7">
                                <pre class="mb-0 small bg-light p-3 border">{{ json_encode(json_decode($hook->payload_json), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Sin webhooks registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $webhooks->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
