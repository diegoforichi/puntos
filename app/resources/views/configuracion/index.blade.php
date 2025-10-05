@extends('layouts.app')

@section('title', 'Configuración')
@section('page-title', 'Configuración del Sistema')

@section('content')
<div class="container-fluid">
    <!-- Tabs de Configuración -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#puntos" type="button">
                <i class="bi bi-award me-2"></i>
                Puntos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#contacto" type="button">
                <i class="bi bi-building me-2"></i>
                Datos de Contacto
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#whatsapp" type="button">
                <i class="bi bi-whatsapp me-2"></i>
                WhatsApp
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#mantenimiento" type="button">
                <i class="bi bi-tools me-2"></i>
                Mantenimiento
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Tab Puntos -->
        <div class="tab-pane fade show active" id="puntos" role="tabpanel">
            <div class="row">
                <!-- Puntos por Pesos -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-calculator me-2"></i>
                                Conversión de Puntos
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="/{{ $tenant->rut }}/configuracion/puntos" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label for="puntos_por_pesos" class="form-label">
                                        Cada <strong>cuántos pesos</strong> equivalen a <strong>1 punto</strong>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input 
                                            type="number" 
                                            class="form-control @error('puntos_por_pesos') is-invalid @enderror" 
                                            id="puntos_por_pesos" 
                                            name="puntos_por_pesos" 
                                            value="{{ old('puntos_por_pesos', $puntosConfig['valor']) }}"
                                            min="1"
                                            step="0.01"
                                            required
                                        >
                                        <span class="input-group-text">= 1 punto</span>
                                    </div>
                                    @error('puntos_por_pesos')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Ejemplo: Si pones 100, cada $100 de compra = 1 punto
                                    </small>
                                </div>

                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Configuración actual:</strong> Cada ${{ $puntosConfig['valor'] }} = 1 punto
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Guardar Configuración
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Días de Vencimiento -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar-x me-2"></i>
                                Vencimiento de Puntos
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="/{{ $tenant->rut }}/configuracion/vencimiento" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label for="dias_vencimiento" class="form-label">
                                        Los puntos vencen después de
                                    </label>
                                    <div class="input-group">
                                        <input 
                                            type="number" 
                                            class="form-control @error('dias_vencimiento') is-invalid @enderror" 
                                            id="dias_vencimiento" 
                                            name="dias_vencimiento" 
                                            value="{{ old('dias_vencimiento', $vencimientoConfig['valor']) }}"
                                            min="1"
                                            required
                                        >
                                        <span class="input-group-text">días</span>
                                    </div>
                                    @error('dias_vencimiento')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Ejemplo: 180 días = 6 meses
                                    </small>
                                </div>

                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Configuración actual:</strong> Los puntos vencen en {{ $vencimientoConfig['valor'] }} días
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Guardar Configuración
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-funnel me-2"></i>
                                Reglas de Acumulación
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Define si deseas excluir las e-Facturas del cálculo de puntos.</p>
                            <form action="/{{ $tenant->rut }}/configuracion/acumulacion" method="POST">
                                @csrf
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="acumulacion_excluir_efacturas" name="acumulacion_excluir_efacturas" {{ ($configAcumulacion['excluir_efacturas'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="acumulacion_excluir_efacturas">
                                        Excluir e-Facturas (solo acumula e-Tickets y otros comprobantes)
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-save me-2"></i>
                                    Guardar Reglas
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-currency-exchange me-2"></i>
                                Moneda y Conversión
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="/{{ $tenant->rut }}/configuracion/moneda" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Moneda base</label>
                                        <input type="text" name="moneda_base" class="form-control" value="{{ old('moneda_base', $configMoneda['moneda_base']) }}" maxlength="10" required>
                                        <small class="text-muted">Ej. UYU, ARS</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tasa USD → base</label>
                                        <input type="number" step="0.01" min="0" name="tasa_usd" class="form-control" value="{{ old('tasa_usd', $configMoneda['tasa_usd']) }}" required>
                                        <small class="text-muted">1 USD equivale a...</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Moneda sin tasa</label>
                                        <select name="moneda_desconocida" class="form-select">
                                            <option value="omitir" {{ $configMoneda['moneda_desconocida'] === 'omitir' ? 'selected' : '' }}>Omitir (no acumula)</option>
                                            <option value="sin_convertir" {{ $configMoneda['moneda_desconocida'] === 'sin_convertir' ? 'selected' : '' }}>Procesar sin convertir</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-outline-primary mt-3">
                                    <i class="bi bi-save me-2"></i>
                                    Guardar configuración de moneda
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Contacto -->
        <div class="tab-pane fade" id="contacto" role="tabpanel">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-building me-2"></i>
                                Información de Contacto
                            </h5>
                            <small class="text-muted">Esta información aparece en el portal público de consulta</small>
                        </div>
                        <div class="card-body">
                            <form action="/{{ $tenant->rut }}/configuracion/contacto" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label for="nombre_comercial" class="form-label">Nombre Comercial *</label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('nombre_comercial') is-invalid @enderror" 
                                        id="nombre_comercial" 
                                        name="nombre_comercial" 
                                        value="{{ old('nombre_comercial', $contactoConfig['nombre_comercial']) }}"
                                        required
                                    >
                                    @error('nombre_comercial')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Teléfono de Contacto</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="telefono" 
                                        name="telefono" 
                                        value="{{ old('telefono', $contactoConfig['telefono']) }}"
                                        placeholder="Número telefónico"
                                    >
                                </div>

                                <div class="mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <textarea 
                                        class="form-control" 
                                        id="direccion" 
                                        name="direccion" 
                                        rows="2"
                                        placeholder="Dirección completa"
                                    >{{ old('direccion', $contactoConfig['direccion']) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email de Contacto</label>
                                    <input 
                                        type="email" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        id="email" 
                                        name="email" 
                                        value="{{ old('email', $contactoConfig['email']) }}"
                                        placeholder="Correo del comercio"
                                    >
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Guardar Datos de Contacto
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab WhatsApp -->
        <div class="tab-pane fade" id="whatsapp" role="tabpanel">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-whatsapp me-2"></i>
                                Eventos de WhatsApp
                            </h5>
                            <small class="text-muted">Selecciona qué eventos enviarán notificaciones por WhatsApp</small>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Nota:</strong> La configuración del servicio de WhatsApp (token, URL) se gestiona desde el panel de SuperAdmin.
                            </div>

                            <form action="/{{ $tenant->rut }}/configuracion/whatsapp" method="POST">
                                @csrf
                                
                                <div class="list-group mb-4">
                                    <div class="list-group-item">
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                id="puntos_canjeados" 
                                                name="puntos_canjeados"
                                                {{ $eventosWhatsApp['puntos_canjeados'] ?? false ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="puntos_canjeados">
                                                <strong>Puntos Canjeados</strong>
                                                <br>
                                                <small class="text-muted">Notificar al cliente cuando se canjeen puntos</small>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                id="puntos_por_vencer" 
                                                name="puntos_por_vencer"
                                                {{ $eventosWhatsApp['puntos_por_vencer'] ?? false ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="puntos_por_vencer">
                                                <strong>Puntos por Vencer</strong>
                                                <br>
                                                <small class="text-muted">Notificar cuando los puntos estén próximos a vencer</small>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                id="promociones_activas" 
                                                name="promociones_activas"
                                                {{ $eventosWhatsApp['promociones_activas'] ?? false ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="promociones_activas">
                                                <strong>Promociones Activas</strong>
                                                <br>
                                                <small class="text-muted">Notificar cuando hay promociones activas</small>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                id="bienvenida_nuevos" 
                                                name="bienvenida_nuevos"
                                                {{ $eventosWhatsApp['bienvenida_nuevos'] ?? false ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="bienvenida_nuevos">
                                                <strong>Bienvenida a Nuevos Clientes</strong>
                                                <br>
                                                <small class="text-muted">Enviar mensaje de bienvenida cuando se registra un nuevo cliente</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Guardar Configuración de WhatsApp
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Mantenimiento -->
        <div class="tab-pane fade" id="mantenimiento" role="tabpanel">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-warning">
                        <div class="card-header bg-warning bg-opacity-10">
                            <h5 class="mb-0">
                                <i class="bi bi-hdd-stack me-2"></i>
                                Compactar Base de Datos
                            </h5>
                            <small class="text-muted">Optimiza el tamaño de la base de datos eliminando registros antiguos</small>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Esta operación eliminará <strong>facturas procesadas</strong> con más de <strong>12 meses</strong> de antigüedad.
                                Los puntos generados por esas facturas <strong>permanecen intactos</strong> en los clientes.
                            </div>

                            <div class="mb-3">
                                <h6>Estadísticas actuales:</h6>
                                <ul class="list-unstyled">
                                    <li>📊 <strong>Total de facturas:</strong> {{ DB::connection('tenant')->table('facturas')->count() }}</li>
                                    <li>🗑️ <strong>Facturas antiguas (>12 meses):</strong> {{ DB::connection('tenant')->table('facturas')->where('created_at', '<', now()->subMonths(12))->count() }}</li>
                                    <li>💾 <strong>Tamaño aproximado:</strong> {{ round(filesize($tenant->getSqlitePath()) / 1024 / 1024, 2) }} MB</li>
                                </ul>
                            </div>

                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Precaución:</strong> Esta operación no se puede deshacer. Se recomienda realizar un respaldo antes de continuar.
                            </div>

                            <form action="/{{ $tenant->rut }}/configuracion/compactar" method="POST" 
                                  onsubmit="return confirm('¿Estás seguro de que deseas compactar la base de datos? Esta operación eliminará facturas antiguas y no se puede deshacer.');">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-lg w-100">
                                    <i class="bi bi-archive me-2"></i>
                                    Compactar Base de Datos Ahora
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
