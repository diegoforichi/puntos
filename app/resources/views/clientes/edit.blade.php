@extends('layouts.app')

@section('title', 'Editar Cliente')
@section('page-title')
    <a href="/{{ $tenant->rut }}/clientes/{{ $cliente->id }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
    / Editar Cliente
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil me-2"></i>
                        Editar Datos del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Info del Cliente -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Editando cliente: <strong>{{ $cliente->nombre }}</strong>
                        <br>
                        Documento: <code>{{ $cliente->documento }}</code>
                    </div>

                    <!-- Formulario -->
                    <form action="/{{ $tenant->rut }}/clientes/{{ $cliente->id }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-12 mb-3">
                                <label for="nombre" class="form-label">
                                    <i class="bi bi-person"></i> Nombre Completo *
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control @error('nombre') is-invalid @enderror" 
                                    id="nombre" 
                                    name="nombre" 
                                    value="{{ old('nombre', $cliente->nombre) }}"
                                    required
                                >
                                @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">
                                    <i class="bi bi-phone"></i> Teléfono
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control @error('telefono') is-invalid @enderror" 
                                    id="telefono" 
                                    name="telefono" 
                                    value="{{ old('telefono', $cliente->telefono) }}"
                                    placeholder="Teléfono"
                                >
                                @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Formato: 09XXXXXXX (Uruguay)
                                </small>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input 
                                    type="email" 
                                    class="form-control @error('email') is-invalid @enderror" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email', $cliente->email) }}"
                                    placeholder="cliente@ejemplo.com"
                                >
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Dirección -->
                            <div class="col-md-12 mb-3">
                                <label for="direccion" class="form-label">
                                    <i class="bi bi-geo-alt"></i> Dirección
                                </label>
                                <textarea 
                                    class="form-control @error('direccion') is-invalid @enderror" 
                                    id="direccion" 
                                    name="direccion" 
                                    rows="2"
                                    placeholder="Dirección completa"
                                >{{ old('direccion', $cliente->direccion) }}</textarea>
                                @error('direccion')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="/{{ $tenant->rut }}/clientes/{{ $cliente->id }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-info-circle"></i> Información Adicional
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Documento:</strong> <code>{{ $cliente->documento }}</code>
                                <br>
                                <small class="text-muted">(No editable)</small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Puntos acumulados:</strong> 
                                <span class="badge bg-success">{{ $cliente->puntos_formateados }}</span>
                                <br>
                                <small class="text-muted">(No editable)</small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Registrado:</strong> {{ $cliente->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Última actividad:</strong> 
                                @if($cliente->ultima_actividad)
                                    {{ $cliente->ultima_actividad->diffForHumans() }}
                                @else
                                    <span class="text-muted">Sin actividad</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
