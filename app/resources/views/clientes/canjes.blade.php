@extends('layouts.app')

@section('title', 'Canjes de ' . $cliente->nombre)
@section('page-title')
    <a href="/{{ $tenant->rut }}/clientes/{{ $cliente->id }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left"></i> {{ $cliente->nombre }}
    </a>
    / Historial de Canjes
@endsection

@section('content')
<div class="container-fluid">
    <!-- Info del Cliente -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Puntos Disponibles</h6>
                    <h3 class="mb-0 {{ $cliente->puntos_acumulados < 0 ? 'text-danger' : 'text-success' }}">{{ $cliente->puntos_formateados }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Canjes</h6>
                    <h3 class="mb-0">{{ $stats['total_canjes'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Puntos Canjeados</h6>
                    <h3 class="mb-0 text-danger">{{ number_format($stats['total_puntos_canjeados'], 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Ajustes</h6>
                    <h3 class="mb-0 text-info">{{ $stats['total_ajustes'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Canjes -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-gift me-2"></i>
                Historial Completo de Canjes - {{ $cliente->nombre }}
            </h5>
            <span class="badge bg-secondary">{{ $canjes->total() }} registro(s)</span>
        </div>
        <div class="card-body p-0">
            @if($canjes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Codigo</th>
                            <th class="text-end">Puntos</th>
                            <th>Concepto</th>
                            <th>Tipo</th>
                            <th>Autorizado Por</th>
                            <th>Saldo Posterior</th>
                            <th>Fecha</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($canjes as $canje)
                        @php
                            $esAjuste = $canje->es_ajuste;
                            $esSuma = $canje->es_ajuste_suma;
                            $signo = $esSuma ? '+' : '-';
                            $puntosClass = $esSuma ? 'text-success' : 'text-danger';
                        @endphp
                        <tr>
                            <td><code>{{ $canje->codigo_cupon }}</code></td>
                            <td class="text-end">
                                <strong class="{{ $puntosClass }}">{{ $signo }}{{ number_format($canje->puntos_canjeados, 2, ',', '.') }}</strong>
                            </td>
                            <td>{{ $canje->concepto }}</td>
                            <td>
                                @if($esAjuste)
                                    <span class="badge bg-info-subtle text-info">Ajuste</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary">Canje</span>
                                @endif
                            </td>
                            <td>{{ $canje->autorizadoPor->nombre ?? 'Sistema' }}</td>
                            <td class="text-end">
                                <small class="text-muted">{{ number_format($canje->puntos_restantes, 2, ',', '.') }}</small>
                            </td>
                            <td>
                                <small>{{ $canje->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                @if(!$esAjuste && in_array($usuario->rol, ['admin', 'supervisor']))
                                <a href="/{{ $tenant->rut }}/puntos/cupon/{{ $canje->id }}/pdf" target="_blank" class="btn btn-outline-secondary btn-sm" title="Reimprimir cupón">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No hay canjes registrados para este cliente</p>
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
                de <span class="fw-semibold">{{ $canjes->total() }}</span> registro(s)
            </small>
            {{ $canjes->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @endif
</div>
@endsection
