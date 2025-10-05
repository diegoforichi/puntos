@extends('layouts.app')

@section('title', 'Reporte de Facturas')
@section('page-title', 'Reporte de Facturas')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <a href="/{{ $tenant->rut }}/reportes" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-2"></i>
                    Volver
                </a>
                <a href="/{{ $tenant->rut }}/reportes/facturas?formato=csv&{{ http_build_query($filtros) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                    Exportar a CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="/{{ $tenant->rut }}/reportes/facturas" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ $filtros['fecha_inicio'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ $filtros['fecha_fin'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todas</option>
                        <option value="activas" {{ ($filtros['estado'] ?? '') === 'activas' ? 'selected' : '' }}>Activas</option>
                        <option value="vencidas" {{ ($filtros['estado'] ?? '') === 'vencidas' ? 'selected' : '' }}>Vencidas</option>
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

    <!-- Resultados -->
    <div class="card">
        <div class="card-body p-0">
            @if($facturas->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Factura</th>
                            <th>Cliente</th>
                            <th class="text-end">Monto</th>
                            <th class="text-end">Puntos</th>
                            <th>Fecha</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($facturas as $factura)
                        <tr>
                            <td><code>{{ $factura->numero_factura }}</code></td>
                            <td>{{ $factura->cliente->nombre }}</td>
                            <td class="text-end">
                                ${{ number_format($factura->monto_total, 2, ',', '.') }}
                                @php($monedaFactura = $factura->moneda ?? ($configMoneda['moneda_base'] ?? null))
                                @if($monedaFactura)
                                    <span class="text-muted">{{ $monedaFactura }}</span>
                                @endif
                            </td>
                            <td class="text-end"><strong>{{ number_format($factura->puntos_generados, 2, ',', '.') }}</strong></td>
                            <td>{{ $factura->fecha_emision->format('d/m/Y') }}</td>
                            <td>{{ $factura->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $factura->estaVencida() ? 'bg-danger' : 'bg-success' }}">
                                    {{ $factura->estaVencida() ? 'Vencida' : 'Activa' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No se encontraron facturas</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
