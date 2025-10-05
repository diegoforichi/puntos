@extends('layouts.app')

@section('title', 'Reporte de Clientes')
@section('page-title', 'Reporte de Clientes')

@section('content')
<div class="container-fluid">
    <!-- Header con botón de exportar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="/{{ $tenant->rut }}/reportes" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-2"></i>
                        Volver a Reportes
                    </a>
                </div>
                <a href="/{{ $tenant->rut }}/reportes/clientes?formato=csv&{{ http_build_query($filtros) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                    Exportar a CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="/{{ $tenant->rut }}/reportes/clientes" method="GET" class="row g-3">
                <!-- Estado -->
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos los clientes</option>
                        <option value="con_puntos" {{ ($filtros['estado'] ?? '') === 'con_puntos' ? 'selected' : '' }}>
                            Solo con puntos
                        </option>
                        <option value="sin_puntos" {{ ($filtros['estado'] ?? '') === 'sin_puntos' ? 'selected' : '' }}>
                            Sin puntos
                        </option>
                    </select>
                </div>

                <!-- Orden -->
                <div class="col-md-4">
                    <label class="form-label">Ordenar por</label>
                    <select name="orden" class="form-select">
                        <option value="puntos_desc" {{ ($filtros['orden'] ?? 'puntos_desc') === 'puntos_desc' ? 'selected' : '' }}>
                            Más puntos primero
                        </option>
                        <option value="puntos_asc" {{ ($filtros['orden'] ?? '') === 'puntos_asc' ? 'selected' : '' }}>
                            Menos puntos primero
                        </option>
                        <option value="nombre_asc" {{ ($filtros['orden'] ?? '') === 'nombre_asc' ? 'selected' : '' }}>
                            Nombre (A-Z)
                        </option>
                        <option value="fecha_desc" {{ ($filtros['orden'] ?? '') === 'fecha_desc' ? 'selected' : '' }}>
                            Más recientes
                        </option>
                    </select>
                </div>

                <!-- Botón -->
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Clientes</h6>
                    <h3 class="mb-0">{{ $clientes->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Puntos Totales</h6>
                    <h3 class="mb-0">{{ number_format($clientes->sum('puntos_acumulados'), 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Promedio por Cliente</h6>
                    <h3 class="mb-0">
                        {{ $clientes->count() > 0 ? number_format($clientes->avg('puntos_acumulados'), 2, ',', '.') : '0,00' }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Resultados -->
    <div class="card">
        <div class="card-body p-0">
            @if($clientes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th class="text-end">Puntos</th>
                            <th>Registrado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $cliente)
                        <tr>
                            <td><code>{{ $cliente->documento }}</code></td>
                            <td>
                                <a href="/{{ $tenant->rut }}/clientes/{{ $cliente->id }}">
                                    {{ $cliente->nombre }}
                                </a>
                            </td>
                            <td>{{ $cliente->telefono ?? '-' }}</td>
                            <td>{{ $cliente->email ?? '-' }}</td>
                            <td class="text-end">
                                <strong class="text-success">{{ $cliente->puntos_formateados }}</strong>
                            </td>
                            <td>
                                <small class="text-muted">{{ $cliente->created_at->format('d/m/Y') }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No se encontraron clientes con los filtros seleccionados</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
