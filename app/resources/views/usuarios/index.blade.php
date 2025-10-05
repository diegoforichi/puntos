@extends('layouts.app')

@section('title', 'Gestión de Usuarios')
@section('page-title', 'Gestión de Usuarios')

@section('content')
<div class="container-fluid">
    <!-- Mensajes -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-people me-2"></i>
                    Usuarios del Sistema
                </h4>
                <a href="/{{ $tenant->rut }}/usuarios/crear" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Nuevo Usuario
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="/{{ $tenant->rut }}/usuarios" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Rol</label>
                    <select name="rol" class="form-select">
                        <option value="">Todos los roles</option>
                        <option value="admin" {{ ($filtros['rol'] ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="supervisor" {{ ($filtros['rol'] ?? '') === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="operario" {{ ($filtros['rol'] ?? '') === 'operario' ? 'selected' : '' }}>Operario</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activos" {{ ($filtros['estado'] ?? '') === 'activos' ? 'selected' : '' }}>Activos</option>
                        <option value="inactivos" {{ ($filtros['estado'] ?? '') === 'inactivos' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body p-0">
            @if($usuarios->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Último Login</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $usr)
                        <tr>
                            <td>
                                <strong>{{ $usr->nombre }}</strong>
                                @if($usr->id === $usuario->id)
                                <span class="badge bg-info">Tú</span>
                                @endif
                            </td>
                            <td><code>{{ $usr->email }}</code></td>
                            <td>
                                <span class="badge {{ $usr->rol === 'admin' ? 'bg-danger' : ($usr->rol === 'supervisor' ? 'bg-warning' : 'bg-secondary') }}">
                                    {{ ucfirst($usr->rol) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $usr->activo ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $usr->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $usr->ultimo_login ? $usr->ultimo_login->diffForHumans() : 'Nunca' }}
                                </small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="/{{ $tenant->rut }}/usuarios/{{ $usr->id }}/editar" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    @if($usr->id !== $usuario->id)
                                    <form action="/{{ $tenant->rut }}/usuarios/{{ $usr->id }}/toggle" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-{{ $usr->activo ? 'warning' : 'success' }}" title="{{ $usr->activo ? 'Desactivar' : 'Activar' }}">
                                            <i class="bi bi-{{ $usr->activo ? 'pause' : 'play' }}-circle"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No se encontraron usuarios</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
