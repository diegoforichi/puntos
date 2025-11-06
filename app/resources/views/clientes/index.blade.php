@extends('layouts.app')

@section('title', 'Gestión de Clientes')
@section('page-title', 'Gestión de Clientes')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Clientes</h1>
            <p class="text-muted mb-0">Gestiona tu base de clientes y sus puntos</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/{{ $tenant->rut }}/clientes/crear" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>
                Nuevo Cliente
            </a>
        </div>
    </div>
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Clientes</p>
                            <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                        <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Con Puntos</p>
                            <h3 class="mb-0">{{ number_format($stats['con_puntos']) }}</h3>
                        </div>
                        <i class="bi bi-trophy text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Activos (30d)</p>
                            <h3 class="mb-0">{{ number_format($stats['activos']) }}</h3>
                        </div>
                        <i class="bi bi-activity text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Puntos Totales</p>
                            <h3 class="mb-0">{{ number_format($stats['total_puntos'], 0) }}</h3>
                        </div>
                        <i class="bi bi-star text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="/{{ $tenant->rut }}/clientes" method="GET" class="row g-3">
                <!-- Búsqueda -->
                <div class="col-md-4">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Buscar
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="search" 
                        name="search" 
                        value="{{ $search }}"
                        placeholder="Documento, nombre o email..."
                    >
                </div>

                <!-- Filtro -->
                <div class="col-md-3">
                    <label for="filtro" class="form-label">
                        <i class="bi bi-funnel"></i> Filtrar por
                    </label>
                    <select class="form-select" id="filtro" name="filtro">
                        <option value="todos" {{ $filtro == 'todos' ? 'selected' : '' }}>Todos</option>
                        <option value="con_puntos" {{ $filtro == 'con_puntos' ? 'selected' : '' }}>Con puntos</option>
                        <option value="activos" {{ $filtro == 'activos' ? 'selected' : '' }}>Activos (30 días)</option>
                    </select>
                </div>

                <!-- Ordenar -->
                <div class="col-md-3">
                    <label for="ordenar" class="form-label">
                        <i class="bi bi-sort-down"></i> Ordenar por
                    </label>
                    <select class="form-select" id="ordenar" name="ordenar">
                        <option value="recientes" {{ $ordenar == 'recientes' ? 'selected' : '' }}>Más recientes</option>
                        <option value="antiguos" {{ $ordenar == 'antiguos' ? 'selected' : '' }}>Más antiguos</option>
                        <option value="puntos_desc" {{ $ordenar == 'puntos_desc' ? 'selected' : '' }}>Más puntos</option>
                        <option value="puntos_asc" {{ $ordenar == 'puntos_asc' ? 'selected' : '' }}>Menos puntos</option>
                    </select>
                </div>

                <!-- Botones -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    @if($search || $filtro != 'todos' || $ordenar != 'recientes')
                    <a href="/{{ $tenant->rut }}/clientes" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>
                Listado de Clientes
                @if($search)
                    <span class="badge bg-info">Resultados de búsqueda: "{{ $search }}"</span>
                @endif
            </h5>
            <span class="text-muted">{{ $clientes->total() }} cliente(s)</span>
        </div>
        <div class="card-body p-0">
            @if($clientes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th>Nombre</th>
                            <th>Contacto</th>
                            <th class="text-end">Puntos</th>
                            <th>Última Actividad</th>
                            <th>Registrado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $cliente)
                        <tr>
                            <td>
                                <code class="text-primary">{{ $cliente->documento }}</code>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-2">
                                        {{ $cliente->iniciales }}
                                    </div>
                                    <strong>{{ $cliente->nombre }}</strong>
                                </div>
                            </td>
                            <td>
                                @if($cliente->telefono)
                                    <small>
                                        <i class="bi bi-phone"></i> {{ $cliente->telefono }}
                                    </small>
                                    <br>
                                @endif
                                @if($cliente->email)
                                    <small class="text-muted">
                                        <i class="bi bi-envelope"></i> {{ $cliente->email }}
                                    </small>
                                @endif
                                @if(!$cliente->telefono && !$cliente->email)
                                    <small class="text-muted">Sin contacto</small>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($cliente->puntos_acumulados > 0)
                                    <span class="badge bg-success" style="font-size: 1rem;">
                                        {{ $cliente->puntos_formateados }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">0</span>
                                @endif
                            </td>
                            <td>
                                @if($cliente->ultima_actividad)
                                    <small>{{ $cliente->ultima_actividad->diffForHumans() }}</small>
                                @else
                                    <small class="text-muted">Sin actividad</small>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $cliente->created_at->format('d/m/Y') }}
                                </small>
                            </td>
                            <td class="text-center">
                                <a 
                                    href="/{{ $tenant->rut }}/clientes/{{ $cliente->id }}" 
                                    class="btn btn-sm btn-outline-primary"
                                    title="Ver detalle"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <small class="text-muted">
                    Mostrando
                    @if($clientes->count() > 0)
                        <span class="fw-semibold">{{ $clientes->firstItem() }}-{{ $clientes->lastItem() }}</span>
                    @else
                        <span class="fw-semibold">0</span>
                    @endif
                    de <span class="fw-semibold">{{ $clientes->total() }}</span> resultado(s)
                </small>
                {{ $clientes->links('vendor.pagination.bootstrap-5') }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                <p class="mt-3 text-muted">
                    @if($search)
                        No se encontraron clientes que coincidan con "{{ $search }}"
                    @else
                        No hay clientes registrados aún
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
    }
</style>
@endsection
