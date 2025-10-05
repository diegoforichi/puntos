@extends('superadmin.layout')

@section('title', 'Dashboard SuperAdmin')
@section('page-title', 'Dashboard Global')
@section('page-subtitle', 'Resumen ejecutivo del sistema')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Tenants Totales</p>
                <h3 class="mb-0">{{ $stats['tenants_total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Tenants Activos</p>
                <h3 class="mb-0 text-success">{{ $stats['tenants_activos'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Facturas Procesadas</p>
                <h3 class="mb-0">{{ number_format($stats['facturas_totales']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Puntos Generados</p>
                <h3 class="mb-0">{{ number_format($stats['puntos_totales']) }}</h3>
            </div>
        </div>
    </div>
</div>

@if(!empty($stats['ultimo_webhook']))
<div class="alert alert-info mb-4">
    <i class="bi bi-clock-history me-2"></i>
    Último webhook recibido: {{ optional($stats['ultimo_webhook'])->format('d/m/Y H:i') }}
</div>
@endif

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Tenants con mayor actividad</h5>
            </div>
            <div class="card-body">
                @forelse($topTenants as $tenant)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <strong>{{ $tenant->nombre_comercial }}</strong>
                            <div class="text-muted small">RUT: {{ $tenant->rut }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold">{{ number_format($tenant->facturas_recibidas) }} facturas</div>
                            <small class="text-muted">{{ number_format($tenant->puntos_generados_total) }} puntos</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Sin datos disponibles.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Últimas acciones registradas</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @forelse($recentLogs as $log)
                        <div class="mb-3">
                            <div class="fw-semibold">{{ $log->accion }}</div>
                            <small class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }} • {{ $log->user->name ?? 'Desconocido' }}</small>
                            @if($log->descripcion)
                                <div>{{ $log->descripcion }}</div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted">Sin acciones registradas aún.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
