@extends('layouts.app')

@section('title', 'Canjear Puntos')
@section('page-title', 'Canjear Puntos')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Alert de Permisos -->
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Información:</strong> 
                Usted puede canjear puntos directamente como {{ $usuario->rol_nombre }}.
            </div>

            <!-- Card Principal -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-gift me-2"></i>
                        Formulario de Canje de Puntos
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Paso 1: Buscar Cliente -->
                    <div id="step-1" class="{{ $cliente ? 'd-none' : '' }}">
                        <h6 class="mb-3">
                            <span class="badge bg-primary me-2">1</span>
                            Buscar Cliente
                        </h6>
                        
                        <form action="/{{ $tenant->rut }}/puntos/buscar-cliente" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <label for="documento_buscar" class="form-label">
                                        <i class="bi bi-search"></i> Documento del Cliente
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        name="documento"
                                        id="documento_buscar"
                                        placeholder="Documento del cliente"
                                        value="{{ old('documento') }}"
                                        required
                                        autofocus
                                    >
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Buscar
                                    </button>
                                </div>
                            </div>

                            @if(session('error_busqueda'))
                                <div class="alert alert-danger mt-3">{{ session('error_busqueda') }}</div>
                            @endif
                        </form>
                    </div>

                    <!-- Paso 2: Datos del Cliente y Canje -->
                    <div id="step-2" class="{{ !$cliente ? 'd-none' : '' }}">
                        <!-- Info del Cliente -->
                        <div id="cliente-info" class="mb-4">
                            @if($cliente)
                            <div class="alert alert-success">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3">
                                        {{ $cliente->iniciales }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $cliente->nombre }}</h6>
                                        <small>
                                            <i class="bi bi-person-badge"></i> {{ $cliente->documento }}
                                            @if($cliente->telefono)
                                                | <i class="bi bi-phone"></i> {{ $cliente->telefono }}
                                            @endif
                                        </small>
                                        <br>
                                        <strong class="text-success">
                                            <i class="bi bi-trophy"></i>
                                            Puntos disponibles: {{ $cliente->puntos_formateados }}
                                        </strong>
                                    </div>
                                    <a href="/{{ $tenant->rut }}/puntos/canjear" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Cambiar
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Formulario de Canje -->
                        <form action="/{{ $tenant->rut }}/puntos/canjear" method="POST" id="form-canje">
                            @csrf
                            <input type="hidden" name="cliente_id" id="cliente_id" value="{{ $cliente->id ?? '' }}">

                            <h6 class="mb-3">
                                <span class="badge bg-primary me-2">2</span>
                                Datos del Canje
                            </h6>

                            <div class="row">
                                <!-- Puntos a Canjear -->
                                <div class="col-md-6 mb-3">
                                    <label for="puntos_a_canjear" class="form-label">
                                        <i class="bi bi-gift"></i> Puntos a Canjear *
                                    </label>
                                    <input 
                                        type="number" 
                                        class="form-control @error('puntos_a_canjear') is-invalid @enderror" 
                                        id="puntos_a_canjear" 
                                        name="puntos_a_canjear" 
                                        step="0.01"
                                        min="0.01"
                                        max="{{ $cliente->puntos_acumulados ?? 0 }}"
                                        value="{{ old('puntos_a_canjear') }}"
                                        required
                                    >
                                    @error('puntos_a_canjear')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Máximo: <span id="puntos-max">{{ $cliente->puntos_formateados ?? '0' }}</span> puntos
                                    </small>
                                </div>

                                <!-- Concepto -->
                                <div class="col-md-6 mb-3">
                                    <label for="concepto" class="form-label">
                                        <i class="bi bi-tag"></i> Concepto
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('concepto') is-invalid @enderror" 
                                        id="concepto" 
                                        name="concepto" 
                                        value="{{ old('concepto', 'Canje de puntos') }}"
                                        placeholder="Ej: Descuento en compra"
                                    >
                                    @error('concepto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Botones Quick -->
                            <div class="mb-3">
                                <label class="form-label">Acciones rápidas:</label>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-percentage="25">
                                        25%
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-percentage="50">
                                        50%
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-percentage="75">
                                        75%
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-percentage="100">
                                        100%
                                    </button>
                                </div>
                            </div>

                            <!-- Resumen -->
                            <div class="alert alert-light border" id="resumen-canje">
                                <h6 class="mb-2"><i class="bi bi-calculator"></i> Resumen del Canje</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">Puntos actuales:</small>
                                        <br>
                                        <strong id="resumen-actuales">{{ $cliente->puntos_formateados ?? '0' }}</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">A canjear:</small>
                                        <br>
                                        <strong class="text-danger" id="resumen-canjear">0</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Quedarán:</small>
                                        <br>
                                        <strong class="text-success" id="resumen-restantes">{{ $cliente->puntos_formateados ?? '0' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Facturas que se eliminarán -->
                            <div class="alert alert-warning" id="facturas-info" style="display: {{ $cliente ? 'block' : 'none' }};">
                                <h6 class="mb-2">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Facturas de Referencia (FIFO)
                                </h6>
                                <small>
                                    Las siguientes facturas serán eliminadas o actualizadas según los puntos canjeados:
                                </small>
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm mb-0" id="facturas-fifo">
                                        <thead>
                                            <tr>
                                                <th>Factura</th>
                                                <th class="text-end">Puntos</th>
                                                <th>Vence</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse(($cliente->facturasActivas ?? []) as $factura)
                                            <tr>
                                                <td><small><code>{{ $factura->numero_factura }}</code></small></td>
                                                <td class="text-end">{{ $factura->puntos_formateados }}</td>
                                                <td><small>{{ $factura->fecha_vencimiento->format('d/m/Y') }}</small></td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">Sin facturas activas</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="/{{ $tenant->rut }}/clientes" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-success btn-lg" id="btn-procesar">
                                    <i class="bi bi-check-circle"></i> Procesar Canje
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
    }
</style>

@push('scripts')
<script>
    // Solo cálculos en tiempo real, sin AJAX
    const puntosInput = document.getElementById('puntos_a_canjear');
    const puntosDisponibles = {{ $cliente->puntos_acumulados ?? 0 }};

    // Actualizar resumen cuando se cambia el valor
    puntosInput?.addEventListener('input', function() {
        const puntosACanjear = parseFloat(this.value) || 0;
        const restantes = puntosDisponibles - puntosACanjear;

        document.getElementById('resumen-canjear').textContent = formatNumber(puntosACanjear);
        document.getElementById('resumen-restantes').textContent = formatNumber(Math.max(restantes, 0));
    });

    // Botones de porcentaje rápido
    document.querySelectorAll('[data-percentage]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const percent = parseInt(this.dataset.percentage);
            const puntos = (puntosDisponibles * percent / 100).toFixed(2);
            puntosInput.value = puntos;
            puntosInput.dispatchEvent(new Event('input'));
        });
    });

    function formatNumber(num) {
        return new Intl.NumberFormat('es-UY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
    }
</script>
@endpush
@endsection
