@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <!-- Total Clientes -->
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Clientes</p>
                            <h3 class="mb-0">{{ $stats['totalClientes'] }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-people" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-activity"></i>
                        {{ $stats['clientesActivos'] }} activos últimos 30 días
                    </small>
                </div>
            </div>
        </div>

        <!-- Total Puntos -->
        <div class="col-md-3 mb-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Puntos Acumulados</p>
                            <h3 class="mb-0">{{ $stats['totalPuntos'] }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-trophy" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-arrow-up"></i>
                        +{{ $stats['puntosGeneradosMes'] }} este mes
                    </small>
                </div>
            </div>
        </div>

        <!-- Facturas del Mes -->
        <div class="col-md-3 mb-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Facturas del Mes</p>
                            <h3 class="mb-0">{{ $stats['facturasMes'] }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-receipt" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-calendar-event"></i>
                        {{ date('F Y') }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Puntos Canjeados -->
        <div class="col-md-3 mb-3">
            <div class="card stat-card danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Canjeados Este Mes</p>
                            <h3 class="mb-0">{{ $stats['puntosCanjeadosMes'] }}</h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-gift" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-check-circle"></i>
                        Puntos redimidos
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    @if($stats['facturasPorVencer'] > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle me-2" style="font-size: 1.5rem;"></i>
                <div>
                    <strong>Atención:</strong> Hay {{ $stats['facturasPorVencer'] }} factura(s) con puntos que vencerán en los próximos 30 días.
                    Se recomienda notificar a los clientes.
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Clientes Recientes -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus me-2"></i>
                        Clientes Recientes
                    </h5>
                    <a href="/{{ $tenant->rut }}/clientes" class="btn btn-sm btn-outline-primary">
                        Ver todos
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($clientesRecientes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Nombre</th>
                                    <th>Puntos</th>
                                    <th>Registrado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clientesRecientes as $cliente)
                                <tr>
                                    <td><code>{{ $cliente->documento }}</code></td>
                                    <td>{{ $cliente->nombre }}</td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ number_format($cliente->puntos_acumulados, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($cliente->created_at)->diffForHumans() }}
                                        </small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">No hay clientes registrados aún</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Actividad Reciente
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($actividadReciente->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($actividadReciente as $actividad)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $actividad->descripcion }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i>
                                        {{ $actividad->usuario->nombre ?? 'Sistema' }}
                                    </small>
                                </div>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($actividad->created_at)->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">No hay actividad reciente</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <a href="/{{ $tenant->rut }}/clientes" class="btn btn-outline-primary btn-lg w-100">
                                <i class="bi bi-search d-block mb-2" style="font-size: 2rem;"></i>
                                Buscar Cliente
                            </a>
                        </div>
                        
                        @if(in_array($usuario->rol, ['admin', 'supervisor']))
                        <div class="col-md-3 mb-3">
                            <a href="/{{ $tenant->rut }}/puntos/canjear" class="btn btn-outline-success btn-lg w-100">
                                <i class="bi bi-gift d-block mb-2" style="font-size: 2rem;"></i>
                                Canjear Puntos
                            </a>
                        </div>
                        @endif
                        
                        <div class="col-md-3 mb-3">
                            <a href="/{{ $tenant->rut }}/reportes" class="btn btn-outline-info btn-lg w-100">
                                <i class="bi bi-file-earmark-bar-graph d-block mb-2" style="font-size: 2rem;"></i>
                                Ver Reportes
                            </a>
                        </div>
                        
                        @if($usuario->rol === 'admin')
                        <div class="col-md-3 mb-3">
                            <a href="/{{ $tenant->rut }}/configuracion" class="btn btn-outline-secondary btn-lg w-100">
                                <i class="bi bi-gear d-block mb-2" style="font-size: 2rem;"></i>
                                Configuración
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
