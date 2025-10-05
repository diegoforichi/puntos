@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <!-- Logo / Header -->
                    <div class="text-center mb-4">
                        <i class="bi bi-shop" style="font-size: 3rem; color: var(--primary-color);"></i>
                        <h3 class="mt-3 mb-1">{{ $tenant->nombre_comercial }}</h3>
                        <p class="text-muted">Sistema de Gestión de Puntos</p>
                    </div>
                    
                    <!-- Login Form -->
                    <form action="/{{ $tenant->rut }}/login" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="login" class="form-label">
                                <i class="bi bi-person-circle me-1"></i> Usuario o Email
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('email') is-invalid @enderror" 
                                id="login" 
                                name="email" 
                                value="{{ old('email') }}"
                                placeholder="usuario o correo"
                                required 
                                autofocus
                            >
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-1"></i> Contraseña
                            </label>
                            <input 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                placeholder="••••••••"
                                required
                            >
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Iniciar Sesión
                            </button>
                        </div>
                    </form>
                    
                    <!-- Footer Info -->
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            ¿Olvidaste tu contraseña? Contacta al administrador
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Portal Público Link -->
            <div class="text-center mt-3">
                <a href="/{{ $tenant->rut }}/consulta" class="text-decoration-none">
                    <i class="bi bi-search me-1"></i>
                    ¿Eres cliente? Consulta tus puntos aquí
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .card {
        border-radius: 1rem;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
    }
</style>
@endsection
