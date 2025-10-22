@extends('layouts.app')

@section('title', 'Reporte de Canjes')
@section('page-title', 'Reporte de Canjes')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <a href="/{{ $tenant->rut }}/reportes" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-2"></i>
                    Volver
                </a>
                <a href="/{{ $tenant->rut }}/reportes/canjes?formato=csv&{{ http_build_query($filtros) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                    Exportar a CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="/{{ $tenant->rut }}/reportes/canjes" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ $filtros['fecha_inicio'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ $filtros['fecha_fin'] ?? '' }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Canjes mostrados</h6>
                    <h3 class="mb-0">{{ $canjes->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Puntos Canjeados (esta página)</h6>
                    <h3 class="mb-0 text-danger">{{ number_format($canjes->sum('puntos_canjeados'), 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div class="card">
        <div class="card-body p-0">
            @if($canjes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th class="text-end">Puntos</th>
                            <th>Concepto</th>
                            <th>Autorizado Por</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($canjes as $canje)
                        <tr>
                            <td><code>{{ $canje->codigo_cupon }}</code></td>
                            <td>{{ $canje->cliente->nombre }}</td>
                            <td class="text-end"><strong class="text-danger">{{ number_format($canje->puntos_canjeados, 2, ',', '.') }}</strong></td>
                            <td>{{ $canje->concepto }}</td>
                            <td>{{ $canje->autorizadoPor->nombre ?? 'Sistema' }}</td>
                            <td>{{ $canje->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No se encontraron canjes</p>
            </div>
            @endif
        </div>
    </div>
    @if($canjes->count() > 0)
    <div class="card mt-3">
        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <small class="text-muted">
                Mostrando
                <span class="fw-semibold">{{ $canjes->firstItem() }}-{{ $canjes->lastItem() }}</span>
                de <span class="fw-semibold">{{ $canjes->total() }}</span> canje(s)
            </small>
            {{ $canjes->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @endif
</div>
@endsection
