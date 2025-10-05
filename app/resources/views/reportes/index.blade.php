@extends('layouts.app')

@section('title', 'Reportes')
@section('page-title', 'Reportes del Sistema')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <p class="lead text-muted">
                Genera reportes detallados del sistema y exportalos a CSV para análisis externo.
            </p>
        </div>
    </div>

    <!-- Cards de Reportes -->
    <div class="row">
        <!-- Reporte de Clientes -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 hover-shadow">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-people-fill text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Reporte de Clientes</h5>
                    <p class="card-text text-muted small">
                        Lista completa de clientes con sus puntos acumulados
                    </p>
                    <a href="/{{ $tenant->rut }}/reportes/clientes" class="btn btn-primary">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Reporte de Facturas -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 hover-shadow">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-receipt-cutoff text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Reporte de Facturas</h5>
                    <p class="card-text text-muted small">
                        Facturas procesadas con puntos generados
                    </p>
                    <a href="/{{ $tenant->rut }}/reportes/facturas" class="btn btn-success">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Reporte de Canjes -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 hover-shadow">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-gift-fill text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Reporte de Canjes</h5>
                    <p class="card-text text-muted small">
                        Historial de canjes realizados por los clientes
                    </p>
                    <a href="/{{ $tenant->rut }}/reportes/canjes" class="btn btn-danger">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Reporte de Actividad -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 hover-shadow">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-clock-history text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Registro de Actividades</h5>
                    <p class="card-text text-muted small">
                        Log de acciones realizadas en el sistema
                    </p>
                    <a href="/{{ $tenant->rut }}/reportes/actividades" class="btn btn-warning">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Ver Reporte
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h6 class="alert-heading">
                    <i class="bi bi-info-circle me-2"></i>
                    Sobre los Reportes
                </h6>
                <ul class="mb-0 small">
                    <li>Todos los reportes pueden exportarse a formato CSV</li>
                    <li>Los archivos CSV son compatibles con Excel y Google Sheets</li>
                    <li>Puedes aplicar filtros antes de exportar</li>
                    <li>Los datos se exportan con la configuración regional correcta (comas y puntos)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow {
        transition: all 0.3s;
    }
    
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
@endsection
