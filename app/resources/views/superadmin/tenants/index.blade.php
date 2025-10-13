@extends('superadmin.layout')

@section('title', 'Gestión de Tenants')
@section('page-title', 'Tenants')
@section('page-subtitle', 'Comercios conectados al sistema de puntos')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-building-add me-2"></i>Crear nuevo tenant</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('superadmin.tenants.store') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-3">
                <label class="form-label">RUT</label>
                <input type="text" name="rut" class="form-control @error('rut') is-invalid @enderror" value="{{ old('rut') }}" required>
                @error('rut')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Nombre Comercial</label>
                <input type="text" name="nombre_comercial" class="form-control" value="{{ old('nombre_comercial') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Email Contacto</label>
                <input type="email" name="email_contacto" class="form-control" value="{{ old('email_contacto') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono_contacto" class="form-control" value="{{ old('telefono_contacto') }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Dirección</label>
                <input type="text" name="direccion_contacto" class="form-control" value="{{ old('direccion_contacto') }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Crear Tenant
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-list-task me-2"></i>Listado de tenants</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>RUT</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Facturas</th>
                        <th>Puntos</th>
                        <th>Último Webhook</th>
                        <th>URL de Acceso</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        <tr>
                            <td><code>{{ $tenant->rut }}</code></td>
                            <td>{{ $tenant->nombre_comercial }}</td>
                            <td>
                                <span class="badge bg-{{ $tenant->estado === 'activo' ? 'success' : ($tenant->estado === 'suspendido' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($tenant->estado) }}
                                </span>
                            </td>
                            <td>{{ number_format($tenant->facturas_recibidas) }}</td>
                            <td>{{ number_format($tenant->puntos_generados_total) }}</td>
                            <td>{{ $tenant->ultimo_webhook ? $tenant->ultimo_webhook->format('d/m/Y H:i') : '—' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="/{{ $tenant->rut }}/login" target="_blank" class="text-decoration-none">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> /{{ $tenant->rut }}/login
                                    </a>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ url("/{$tenant->rut}/login") }}')" title="Copiar URL">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('superadmin.tenants.show', $tenant) }}" class="btn btn-sm btn-outline-info" title="Detalle del tenant">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tenantModal{{ $tenant->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('superadmin.tenants.regenerate', $tenant) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Regenerar API Key">
                                            <i class="bi bi-key"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('superadmin.tenants.toggle', $tenant) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Cambiar estado">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        @push('modals')
                            <div class="modal fade" id="tenantModal{{ $tenant->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="{{ route('superadmin.tenants.update', $tenant) }}" method="POST" class="modal-content">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Editar tenant {{ $tenant->nombre_comercial }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre Comercial</label>
                                                <input type="text" name="nombre_comercial" class="form-control" value="{{ old('nombre_comercial', $tenant->nombre_comercial) }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Estado</label>
                                                <select name="estado" class="form-select" required>
                                                    @foreach(['activo','suspendido','eliminado'] as $estado)
                                                        <option value="{{ $estado }}" {{ $tenant->estado === $estado ? 'selected' : '' }}>{{ ucfirst($estado) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Formato de Factura</label>
                                                <input type="text" name="formato_factura" class="form-control" value="{{ old('formato_factura', $tenant->formato_factura) }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Contacto</label>
                                                <input type="text" name="nombre_contacto" class="form-control" value="{{ old('nombre_contacto', $tenant->nombre_contacto) }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email_contacto" class="form-control" value="{{ old('email_contacto', $tenant->email_contacto) }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Teléfono</label>
                                                <input type="text" name="telefono_contacto" class="form-control" value="{{ old('telefono_contacto', $tenant->telefono_contacto) }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Dirección</label>
                                                <input type="text" name="direccion_contacto" class="form-control" value="{{ old('direccion_contacto', $tenant->direccion_contacto) }}">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endpush
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No hay tenants registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $tenants->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
