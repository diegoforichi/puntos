@extends('layouts.app')

@section('title', 'Editar Usuario')
@section('page-title', 'Editar Usuario')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <!-- Datos del Usuario -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Información del Usuario</h5>
                </div>
                <div class="card-body">
                    <form action="/{{ $tenant->rut }}/usuarios/{{ $usuarioEditar->id }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre Completo *</label>
                            <input 
                                type="text" 
                                class="form-control @error('nombre') is-invalid @enderror" 
                                id="nombre" 
                                name="nombre" 
                                value="{{ old('nombre', $usuarioEditar->nombre) }}"
                                required
                            >
                            @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Usuario -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario *</label>
                            <input 
                                type="text" 
                                class="form-control @error('username') is-invalid @enderror" 
                                id="username" 
                                name="username" 
                                value="{{ old('username', $usuarioEditar->username) }}"
                                required
                            >
                            @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                type="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                id="email" 
                                name="email" 
                                value="{{ old('email', $usuarioEditar->email) }}"
                            >
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Rol -->
                        <div class="mb-3">
                            <label for="rol" class="form-label">Rol *</label>
                            <select 
                                class="form-select @error('rol') is-invalid @enderror" 
                                id="rol" 
                                name="rol"
                                required
                            >
                                <option value="admin" {{ old('rol', $usuarioEditar->rol) === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="supervisor" {{ old('rol', $usuarioEditar->rol) === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                                <option value="operario" {{ old('rol', $usuarioEditar->rol) === 'operario' ? 'selected' : '' }}>Operario</option>
                            </select>
                            @error('rol')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Activo -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="activo" 
                                    name="activo"
                                    {{ old('activo', $usuarioEditar->activo) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="activo">
                                    Usuario activo
                                </label>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="/{{ $tenant->rut }}/usuarios" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cambiar Contraseña -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-key me-2"></i>
                        Cambiar Contraseña
                    </h5>
                </div>
                <div class="card-body">
                    <form action="/{{ $tenant->rut }}/usuarios/{{ $usuarioEditar->id }}/cambiar-password" method="POST">
                        @csrf

                        <!-- Nueva Contraseña -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva Contraseña *</label>
                            <input 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password"
                            >
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Mínimo 6 caracteres</small>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña *</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password_confirmation" 
                                name="password_confirmation"
                            >
                        </div>

                        <!-- Botón -->
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-key me-2"></i>
                            Cambiar Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
