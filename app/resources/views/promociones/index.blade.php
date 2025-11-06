@extends('layouts.app')

@section('title', 'Promociones')
@section('page-title', 'Gestión de Promociones')

@section('content')
<div class="container-fluid">
    <!-- Header con botón -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-tag me-2"></i>
                    Promociones Activas
                </h4>
                <a href="/{{ $tenant->rut }}/promociones/crear" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Nueva Promoción
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="/{{ $tenant->rut }}/promociones" method="GET" class="row g-3">
                <!-- Buscador -->
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input 
                        type="text" 
                        name="buscar" 
                        class="form-control" 
                        placeholder="Nombre o descripción..."
                        value="{{ $filtros['buscar'] ?? '' }}"
                    >
                </div>

                <!-- Estado -->
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todas</option>
                        <option value="activa" {{ ($filtros['estado'] ?? '') === 'activa' ? 'selected' : '' }}>
                            Activas
                        </option>
                        <option value="inactiva" {{ ($filtros['estado'] ?? '') === 'inactiva' ? 'selected' : '' }}>
                            Inactivas
                        </option>
                    </select>
                </div>

                <!-- Tipo -->
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="descuento" {{ ($filtros['tipo'] ?? '') === 'descuento' ? 'selected' : '' }}>
                            Descuento
                        </option>
                        <option value="bonificacion" {{ ($filtros['tipo'] ?? '') === 'bonificacion' ? 'selected' : '' }}>
                            Bonificación
                        </option>
                        <option value="multiplicador" {{ ($filtros['tipo'] ?? '') === 'multiplicador' ? 'selected' : '' }}>
                            Multiplicador
                        </option>
                    </select>
                </div>

                <!-- Botones -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Promociones -->
    <div class="card">
        <div class="card-body p-0">
            @if($promociones->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                            <th>Prioridad</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($promociones as $promocion)
                        <tr>
                            <!-- Nombre -->
                            <td>
                                <strong>{{ $promocion->nombre }}</strong>
                                @if($promocion->descripcion)
                                <br>
                                <small class="text-muted">{{ Str::limit($promocion->descripcion, 50) }}</small>
                                @endif
                            </td>

                            <!-- Tipo -->
                            <td>
                                <span class="badge {{ $promocion->tipo === 'descuento' ? 'bg-info' : ($promocion->tipo === 'bonificacion' ? 'bg-success' : 'bg-warning') }}">
                                    {{ $promocion->tipo_nombre }}
                                </span>
                            </td>

                            <!-- Valor -->
                            <td>
                                <strong>{{ $promocion->valor_descripcion }}</strong>
                            </td>

                            <!-- Vigencia -->
                            <td>
                                <small>
                                    <i class="bi bi-calendar-event"></i>
                                    {{ $promocion->fecha_inicio->format('d/m/Y') }}
                                    <br>
                                    <i class="bi bi-calendar-x"></i>
                                    {{ $promocion->fecha_fin->format('d/m/Y') }}
                                </small>
                            </td>

                            <!-- Estado -->
                            <td>
                                <span class="badge {{ $promocion->badge_estado['class'] }}">
                                    {{ $promocion->badge_estado['text'] }}
                                </span>
                            </td>

                            <!-- Prioridad -->
                            <td>
                                <span class="badge bg-secondary">{{ $promocion->prioridad }}</span>
                            </td>

                            <!-- Acciones -->
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <!-- Editar -->
                                    <a 
                                        href="/{{ $tenant->rut }}/promociones/{{ $promocion->id }}/editar" 
                                        class="btn btn-outline-primary"
                                        title="Editar"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <!-- Toggle Estado -->
                                    <form 
                                        action="/{{ $tenant->rut }}/promociones/{{ $promocion->id }}/toggle" 
                                        method="POST" 
                                        class="d-inline"
                                    >
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="btn btn-outline-{{ $promocion->activa ? 'warning' : 'success' }}"
                                            title="{{ $promocion->activa ? 'Desactivar' : 'Activar' }}"
                                        >
                                            <i class="bi bi-{{ $promocion->activa ? 'pause' : 'play' }}-circle"></i>
                                        </button>
                                    </form>

                                    @if($promocion->activa)
                                    <form
                                        action="/{{ $tenant->rut }}/promociones/{{ $promocion->id }}/notificar"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('¿Enviar esta promoción por WhatsApp a todos los clientes con teléfono?');"
                                    >
                                        @csrf
                                        <button
                                            type="submit"
                                            class="btn btn-outline-success"
                                            title="Notificar clientes"
                                        >
                                            <i class="bi bi-megaphone"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <!-- Eliminar -->
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger"
                                        onclick="confirmarEliminacion({{ $promocion->id }}, '{{ $promocion->nombre }}')"
                                        title="Eliminar"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($promociones->hasPages())
            <div class="p-3">
                {{ $promociones->links() }}
            </div>
            @endif

            @else
            <!-- Sin resultados -->
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    @if($filtros['buscar'] || $filtros['estado'] || $filtros['tipo'])
                        No se encontraron promociones con los filtros seleccionados
                    @else
                        No hay promociones registradas
                    @endif
                </p>
                <a href="/{{ $tenant->rut }}/promociones/crear" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Crear Primera Promoción
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de eliminar la promoción <strong id="nombrePromocion"></strong>?
                <br><br>
                <small class="text-muted">Esta acción no se puede deshacer.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formEliminar" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmarEliminacion(id, nombre) {
        document.getElementById('nombrePromocion').textContent = nombre;
        document.getElementById('formEliminar').action = '/{{ $tenant->rut }}/promociones/' + id;
        new bootstrap.Modal(document.getElementById('modalEliminar')).show();
    }
</script>
@endpush
@endsection
