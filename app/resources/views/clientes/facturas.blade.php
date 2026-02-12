@extends('layouts.app')

@section('title', 'Facturas de ' . $cliente->nombre)
@section('page-title')
    <a href="/{{ $tenant->rut }}/clientes/{{ $cliente->id }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left"></i> {{ $cliente->nombre }}
    </a>
    / Historial de Facturas
@endsection

@section('content')
<div class="container-fluid">
    <!-- Info del Cliente -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Puntos Disponibles</h6>
                    <h3 class="mb-0 {{ $cliente->puntos_acumulados < 0 ? 'text-danger' : 'text-success' }}">{{ $cliente->puntos_formateados }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Facturas</h6>
                    <h3 class="mb-0">{{ $facturas->total() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Documento</h6>
                    <h3 class="mb-0"><code>{{ $cliente->documento }}</code></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Facturas -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-receipt-cutoff me-2"></i>
                Historial Completo de Facturas - {{ $cliente->nombre }}
            </h5>
            <span class="badge bg-secondary">{{ $facturas->total() }} factura(s)</span>
        </div>
        <div class="card-body p-0">
            @if($facturas->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N Factura</th>
                            <th class="text-end">Monto</th>
                            <th class="text-end">Puntos</th>
                            <th>Fecha Emision</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($facturas as $factura)
                        <tr>
                            <td><code>{{ $factura->numero_factura }}</code></td>
                            <td class="text-end">
                                ${{ number_format($factura->monto_total, 2, ',', '.') }}
                                @if(!empty($factura->moneda))
                                    <span class="text-muted">{{ $factura->moneda }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong>{{ number_format($factura->puntos_generados, 2, ',', '.') }}</strong>
                            </td>
                            <td>{{ $factura->fecha_emision->format('d/m/Y') }}</td>
                            <td>{{ $factura->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $factura->badge_estado['class'] }}">
                                    {{ $factura->badge_estado['text'] }}
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
                <p class="text-muted mt-3">No hay facturas registradas para este cliente</p>
            </div>
            @endif
        </div>
    </div>

    @if($facturas->count() > 0)
    <div class="card mt-3">
        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <small class="text-muted">
                Mostrando
                <span class="fw-semibold">{{ $facturas->firstItem() }}-{{ $facturas->lastItem() }}</span>
                de <span class="fw-semibold">{{ $facturas->total() }}</span> factura(s)
            </small>
            {{ $facturas->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @endif
</div>
@endsection
