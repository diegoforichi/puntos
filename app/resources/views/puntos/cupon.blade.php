@extends('layouts.app')

@section('title', 'Cupón de Canje')
@section('page-title', 'Cupón de Canje Generado')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Alerta de Éxito -->
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2" style="font-size: 1.5rem;"></i>
                <strong>¡Canje realizado exitosamente!</strong>
                <br>
                El cupón ha sido generado y está listo para ser entregado al cliente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <!-- Cupón -->
            <div class="card border-success" id="cupon-canje">
                <div class="card-header bg-success text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="bi bi-ticket-perforated me-2"></i>
                        CUPÓN DE CANJE
                    </h3>
                    <p class="mb-0 mt-2">{{ $tenant->nombre_comercial }}</p>
                </div>

                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                        <h2 class="mt-3">¡Canje Realizado!</h2>
                        <p class="text-muted mb-0">Código de cupón: <strong>{{ $canje->codigo_cupon }}</strong></p>
                    </div>
                </div>

                <div class="card-footer text-center bg-light">
                    <small class="text-muted">
                        Generado el {{ now()->format('d/m/Y H:i:s') }} | Sistema de Puntos v1.3
                    </small>
                </div>
            </div>

            <!-- Acciones Principales -->
            <div class="row mt-4">
                <div class="col-md-6 mb-3">
                    <a href="/{{ $tenant->rut }}/puntos/cupon/{{ $canje->id }}/pdf" target="_blank" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-file-earmark-pdf me-2"></i>
                        Descargar PDF / Imprimir
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <a href="/{{ $tenant->rut }}/puntos/canjear" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nuevo Canje
                    </a>
                </div>
            </div>

            <!-- Acciones Secundarias -->
            <div class="row">
                @if(in_array($usuario->rol, ['admin', 'supervisor']))
                <div class="col-md-6 mb-3">
                    <a href="/{{ $tenant->rut }}/clientes/{{ $canje->cliente_id }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-person me-2"></i>
                        Ver Cliente / Reimprimir
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                @else
                <div class="col-12 mb-3">
                @endif
                    <a href="/{{ $tenant->rut }}/dashboard" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-house me-2"></i>
                        Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para impresión */
    @media print {
        body * {
            visibility: hidden;
        }
        
        #cupon-canje,
        #cupon-canje * {
            visibility: visible;
        }
        
        #cupon-canje {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .sidebar,
        .navbar-custom,
        .btn,
        .alert {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
        }
    }
</style>
@endsection
