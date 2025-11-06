@extends('layouts.app')

@section('title', 'Detalle del Cliente')
@section('page-title')
    <a href="/{{ $tenant->rut }}/clientes" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left"></i> Clientes
    </a>
    / {{ $cliente->nombre }}
@endsection

@section('content')
<div class="container-fluid">
    <!-- Info del Cliente -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="avatar-circle-large me-4">
                            {{ $cliente->iniciales }}
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="mb-1">{{ $cliente->nombre }}</h3>
                            <p class="text-muted mb-3">
                                <i class="bi bi-person-badge"></i> Documento: <code>{{ $cliente->documento }}</code>
                            </p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    @if($cliente->telefono)
                                    <p class="mb-2">
                                        <i class="bi bi-phone text-primary"></i>
                                        <strong>Teléfono:</strong> {{ $cliente->telefono }}
                                    </p>
                                    @endif
                                    
                                    @if($cliente->email)
                                    <p class="mb-2">
                                        <i class="bi bi-envelope text-primary"></i>
                                        <strong>Email:</strong> {{ $cliente->email }}
                                    </p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    @if($cliente->direccion)
                                    <p class="mb-2">
                                        <i class="bi bi-geo-alt text-primary"></i>
                                        <strong>Dirección:</strong> {{ $cliente->direccion }}
                                    </p>
                                    @endif
                                    
                                    <p class="mb-2">
                                        <i class="bi bi-calendar text-primary"></i>
                                        <strong>Cliente desde:</strong> {{ $cliente->created_at->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>
                            
                            @if(in_array($usuario->rol, ['admin', 'supervisor']))
                            <div class="mt-3">
                                <a href="/{{ $tenant->rut }}/clientes/{{ $cliente->id }}/editar" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Editar Datos
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats del Cliente -->
        <div class="col-md-4">
            <div class="card stat-card success h-100">
                <div class="card-body text-center">
                    <i class="bi bi-trophy {{ $cliente->puntos_acumulados < 0 ? 'text-danger' : 'text-success' }}" style="font-size: 3rem;"></i>
                    <h2 class="mt-3 mb-1 {{ $cliente->puntos_acumulados < 0 ? 'text-danger' : '' }}">{{ $cliente->puntos_formateados }}</h2>
                    <p class="text-muted mb-0">Puntos Disponibles</p>
                    
                    @if(in_array($usuario->rol, ['admin', 'supervisor']))
                    <div class="d-grid gap-2 mt-3">
                        @if($cliente->puntos_acumulados > 0)
                        <a href="/{{ $tenant->rut }}/puntos/canjear?cliente_id={{ $cliente->id }}" class="btn btn-success btn-sm">
                            <i class="bi bi-gift"></i> Canjear Puntos
                        </a>
                        @endif
                        <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjustarPuntos">
                            <i class="bi bi-sliders"></i> Ajustar puntos
                        </button>
                    </div>
                    @endif

                    @if($cliente->puntos_acumulados < 0)
                        <p class="text-danger small mt-3 mb-0">
                            Este cliente tiene puntos pendientes de reintegrar.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Detalladas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-receipt text-primary" style="font-size: 2rem;"></i>
                    <h4 class="mt-2">{{ $stats['total_facturas'] }}</h4>
                    <p class="text-muted mb-0">Total Facturas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-up-circle text-success" style="font-size: 2rem;"></i>
                    <h4 class="mt-2">{{ number_format($stats['puntos_generados_total'], 0) }}</h4>
                    <p class="text-muted mb-0">Puntos Generados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-down-circle text-danger" style="font-size: 2rem;"></i>
                    <h4 class="mt-2">{{ number_format($stats['puntos_canjeados_total'], 0) }}</h4>
                    <p class="text-muted mb-0">Puntos Canjeados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-warning" style="font-size: 2rem;"></i>
                    <h4 class="mt-2">{{ number_format($stats['puntos_vencidos_total'], 0) }}</h4>
                    <p class="text-muted mb-0">Puntos Vencidos</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Facturas Activas -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt-cutoff me-2"></i>
                        Facturas Activas
                    </h5>
                    <span class="badge bg-primary">{{ $facturasActivas->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($facturasActivas->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Factura</th>
                                    <th class="text-end">Monto</th>
                                    <th class="text-end">Puntos</th>
                                    <th>Vence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($facturasActivas as $factura)
                                <tr>
                                    <td><small><code>{{ $factura->numero_factura }}</code></small></td>
                                    <td class="text-end">
                                        <small>
                                            {{ $factura->monto_formateado }}
                                            @if(!empty($factura->moneda))
                                                <span class="text-muted">{{ $factura->moneda }}</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success">{{ $factura->puntos_formateados }}</span>
                                    </td>
                                    <td>
                                        <small>
                                            <span class="badge {{ $factura->badge_estado['class'] }}">
                                                {{ $factura->badge_estado['text'] }}
                                            </span>
                                        </small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No hay facturas activas</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Historial de Canjes -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-gift me-2"></i>
                        Historial de Canjes
                    </h5>
                    <span class="badge bg-success">{{ $canjes->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($canjes->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($canjes as $canje)
                        @php
                            $esAjuste = $canje->es_ajuste;
                            $esSuma = $canje->es_ajuste_suma;
                            $signo = $esSuma ? '+' : '-';
                            $puntosClass = $esSuma ? 'text-success' : 'text-danger';
                        @endphp
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <strong class="{{ $puntosClass }}">{{ $signo }}{{ $canje->puntos_formateados }}</strong> puntos
                                    @if($esAjuste)
                                        <span class="badge bg-info-subtle text-info ms-2">Ajuste</span>
                                    @endif
                                    <br>
                                    <small class="text-muted">
                                        {{ $canje->concepto ?? 'Canje de puntos' }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i>
                                        {{ $canje->autorizadoPor->nombre ?? 'Sistema' }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">{{ $canje->created_at->format('d/m/Y') }}</small>
                                    <small class="text-muted d-block">{{ $canje->created_at->format('H:i') }}</small>
                                    @if(in_array($usuario->rol, ['admin', 'supervisor']) && $canje->origen !== 'ajuste')
                                    <a href="/{{ $tenant->rut }}/puntos/cupon/{{ $canje->id }}/pdf" target="_blank" class="btn btn-outline-secondary btn-sm mt-2">
                                        <i class="bi bi-printer"></i> Reimprimir
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No hay canjes registrados</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Puntos Vencidos (si hay) -->
    @if($puntosVencidos->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Puntos Vencidos
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th class="text-end">Puntos Perdidos</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($puntosVencidos as $vencido)
                                <tr>
                                    <td>{{ $vencido->created_at->format('d/m/Y') }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">{{ $vencido->puntos_formateados }}</span>
                                    </td>
                                    <td>{{ $vencido->motivo }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Ajustar Puntos -->
@if(in_array($usuario->rol, ['admin', 'supervisor']))
<div class="modal fade" id="modalAjustarPuntos" tabindex="-1" aria-labelledby="modalAjustarPuntosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/{{ $tenant->rut }}/clientes/{{ $cliente->id }}/ajustar-puntos" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAjustarPuntosLabel">Ajustar puntos del cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo de ajuste</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_ajuste" id="ajusteSumar" value="sumar" {{ old('tipo_ajuste', 'sumar') === 'sumar' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ajusteSumar">Agregar puntos al cliente</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_ajuste" id="ajusteRestar" value="restar" {{ old('tipo_ajuste') === 'restar' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ajusteRestar">Quitar puntos al cliente</label>
                        </div>
                        @error('tipo_ajuste')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ajustePuntos" class="form-label">Cantidad de puntos</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('puntos') is-invalid @enderror" id="ajustePuntos" name="puntos" value="{{ old('puntos') }}" required>
                        @error('puntos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="ajusteMotivo" class="form-label">Motivo del ajuste</label>
                        <textarea class="form-control @error('motivo') is-invalid @enderror" id="ajusteMotivo" name="motivo" rows="3" maxlength="500" required>{{ old('motivo') }}</textarea>
                        @error('motivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-light border mt-3" role="alert">
                        <small class="text-muted">
                            El ajuste quedará registrado en el historial del cliente y no enviará notificación automática.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar ajuste</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<style>
    .avatar-circle-large {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 2rem;
    }
</style>
@if ($errors->has('tipo_ajuste') || $errors->has('puntos') || $errors->has('motivo'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('modalAjustarPuntos');
            if (modal) {
                var instance = bootstrap.Modal.getOrCreateInstance(modal);
                instance.show();
            }
        });
    </script>
@endif
@endsection
