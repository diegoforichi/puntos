@extends('layouts.app')

@section('title', 'Registro de Actividades')
@section('page-title', 'Registro de Actividades')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <a href="/{{ $tenant->rut }}/reportes" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-2"></i>
                    Volver
                </a>
                <a href="/{{ $tenant->rut }}/reportes/actividades?formato=csv&{{ http_build_query($filtros) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                    Exportar a CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="/{{ $tenant->rut }}/reportes/actividades" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ $filtros['fecha_inicio'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ $filtros['fecha_fin'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Acción</label>
                    <select name="accion" class="form-select">
                        <option value="">Todas</option>
                        <option value="login" {{ ($filtros['accion'] ?? '') === 'login' ? 'selected' : '' }}>Login</option>
                        <option value="canje_puntos" {{ ($filtros['accion'] ?? '') === 'canje_puntos' ? 'selected' : '' }}>Canjes</option>
                        <option value="factura_procesada" {{ ($filtros['accion'] ?? '') === 'factura_procesada' ? 'selected' : '' }}>Facturas</option>
                        <option value="promocion_gestionada" {{ ($filtros['accion'] ?? '') === 'promocion_gestionada' ? 'selected' : '' }}>Promociones</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Información -->
    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle me-2"></i>
        Mostrando las últimas <strong>500 actividades</strong> que coinciden con los filtros.
    </div>

    <!-- Resultados -->
    <div class="card">
        <div class="card-body p-0">
            @if($actividades->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($actividades as $actividad)
                        <tr>
                            <td>
                                <small>{{ $actividad->created_at->format('d/m/Y H:i:s') }}</small>
                            </td>
                            <td>{{ $actividad->usuario->nombre ?? 'Sistema' }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $actividad->accion }}</span>
                            </td>
                            <td>{{ $actividad->descripcion }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No se encontraron actividades</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
