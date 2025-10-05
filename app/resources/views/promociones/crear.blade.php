@extends('layouts.app')

@section('title', 'Nueva Promoción')
@section('page-title', 'Crear Nueva Promoción')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-tag me-2"></i>
                        Datos de la Promoción
                    </h5>
                </div>
                <div class="card-body">
                    <form action="/{{ $tenant->rut }}/promociones" method="POST">
                        @csrf

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la Promoción *</label>
                            <input 
                                type="text" 
                                class="form-control @error('nombre') is-invalid @enderror" 
                                id="nombre" 
                                name="nombre" 
                                value="{{ old('nombre') }}"
                                placeholder="Ej: Puntos Dobles - Black Friday"
                                required
                            >
                            @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea 
                                class="form-control @error('descripcion') is-invalid @enderror" 
                                id="descripcion" 
                                name="descripcion" 
                                rows="3"
                                placeholder="Descripción opcional de la promoción..."
                            >{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tipo y Valor (en fila) -->
                        <div class="row">
                            <!-- Tipo -->
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">Tipo de Promoción *</label>
                                <select 
                                    class="form-select @error('tipo') is-invalid @enderror" 
                                    id="tipo" 
                                    name="tipo"
                                    onchange="actualizarValorLabel()"
                                    required
                                >
                                    <option value="">Seleccione...</option>
                                    <option value="descuento" {{ old('tipo') === 'descuento' ? 'selected' : '' }}>
                                        Descuento ($ fijo)
                                    </option>
                                    <option value="bonificacion" {{ old('tipo') === 'bonificacion' ? 'selected' : '' }}>
                                        Bonificación (% extra)
                                    </option>
                                    <option value="multiplicador" {{ old('tipo') === 'multiplicador' ? 'selected' : '' }}>
                                        Multiplicador (2x, 3x puntos)
                                    </option>
                                </select>
                                @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Valor -->
                            <div class="col-md-6 mb-3">
                                <label for="valor" class="form-label" id="valorLabel">Valor *</label>
                                <input 
                                    type="number" 
                                    class="form-control @error('valor') is-invalid @enderror" 
                                    id="valor" 
                                    name="valor" 
                                    value="{{ old('valor') }}"
                                    step="0.01"
                                    min="0"
                                    required
                                >
                                @error('valor')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted" id="valorHelp">
                                    Ingrese el valor según el tipo seleccionado
                                </small>
                            </div>
                        </div>

                        <!-- Fechas (en fila) -->
                        <div class="row">
                            <!-- Fecha Inicio -->
                            <div class="col-md-6 mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha de Inicio *</label>
                                <input 
                                    type="date" 
                                    class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                    id="fecha_inicio" 
                                    name="fecha_inicio" 
                                    value="{{ old('fecha_inicio', date('Y-m-d')) }}"
                                    required
                                >
                                @error('fecha_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha Fin -->
                            <div class="col-md-6 mb-3">
                                <label for="fecha_fin" class="form-label">Fecha de Fin *</label>
                                <input 
                                    type="date" 
                                    class="form-control @error('fecha_fin') is-invalid @enderror" 
                                    id="fecha_fin" 
                                    name="fecha_fin" 
                                    value="{{ old('fecha_fin') }}"
                                    required
                                >
                                @error('fecha_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Condiciones (Acordeón) -->
                        <div class="mb-3">
                            <div class="accordion" id="accordionCondiciones">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button 
                                            class="accordion-button collapsed" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapseCondiciones"
                                        >
                                            <i class="bi bi-sliders me-2"></i>
                                            Condiciones Adicionales (Opcional)
                                        </button>
                                    </h2>
                                    <div id="collapseCondiciones" class="accordion-collapse collapse">
                                        <div class="accordion-body">
                                            <!-- Monto Mínimo -->
                                            <div class="mb-3">
                                                <label for="monto_minimo" class="form-label">Monto Mínimo de Compra</label>
                                                <input 
                                                    type="number" 
                                                    class="form-control" 
                                                    id="monto_minimo" 
                                                    name="monto_minimo" 
                                                    value="{{ old('monto_minimo') }}"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="Ej: 1000"
                                                >
                                                <small class="form-text text-muted">
                                                    Dejar vacío si no aplica
                                                </small>
                                            </div>

                                            <!-- Días de la Semana -->
                                            <div class="mb-3">
                                                <label class="form-label">Días de la Semana</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="dias_semana[]" value="1" id="dia1">
                                                    <label class="form-check-label" for="dia1">Lunes</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="dias_semana[]" value="2" id="dia2">
                                                    <label class="form-check-label" for="dia2">Martes</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="dias_semana[]" value="3" id="dia3">
                                                    <label class="form-check-label" for="dia3">Miércoles</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="dias_semana[]" value="4" id="dia4">
                                                    <label class="form-check-label" for="dia4">Jueves</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="dias_semana[]" value="5" id="dia5">
                                                    <label class="form-check-label" for="dia5">Viernes</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="dias_semana[]" value="6" id="dia6">
                                                    <label class="form-check-label" for="dia6">Sábado</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="dias_semana[]" value="0" id="dia0">
                                                    <label class="form-check-label" for="dia0">Domingo</label>
                                                </div>
                                                <small class="form-text text-muted">
                                                    Si no selecciona ninguno, aplica todos los días
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Prioridad y Estado (en fila) -->
                        <div class="row">
                            <!-- Prioridad -->
                            <div class="col-md-6 mb-3">
                                <label for="prioridad" class="form-label">Prioridad</label>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    id="prioridad" 
                                    name="prioridad" 
                                    value="{{ old('prioridad', 50) }}"
                                    min="0"
                                    max="100"
                                >
                                <small class="form-text text-muted">
                                    0-100. Mayor número = mayor prioridad
                                </small>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Estado Inicial</label>
                                <div class="form-check form-switch">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        id="activa" 
                                        name="activa"
                                        {{ old('activa', true) ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="activa">
                                        Activar automáticamente
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="/{{ $tenant->rut }}/promociones" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>
                                Crear Promoción
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function actualizarValorLabel() {
        const tipo = document.getElementById('tipo').value;
        const valorLabel = document.getElementById('valorLabel');
        const valorHelp = document.getElementById('valorHelp');
        
        if (tipo === 'descuento') {
            valorLabel.textContent = 'Valor del Descuento ($) *';
            valorHelp.textContent = 'Monto fijo a descontar (ej: 100)';
        } else if (tipo === 'bonificacion') {
            valorLabel.textContent = 'Porcentaje de Bonificación (%) *';
            valorHelp.textContent = 'Porcentaje extra de puntos (ej: 20 para 20%)';
        } else if (tipo === 'multiplicador') {
            valorLabel.textContent = 'Factor Multiplicador *';
            valorHelp.textContent = 'Factor de multiplicación (ej: 2 para puntos dobles)';
        } else {
            valorLabel.textContent = 'Valor *';
            valorHelp.textContent = 'Ingrese el valor según el tipo seleccionado';
        }
    }
    
    // Ejecutar al cargar para valores previos
    document.addEventListener('DOMContentLoaded', actualizarValorLabel);
</script>
@endpush
@endsection
