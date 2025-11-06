@extends('layouts.app')

@section('title', 'Campañas')
@section('page-title', 'Campañas de Difusión')
@section('page-subtitle', $tenant->nombre_comercial)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Campañas creadas</h4>
            <small class="text-muted">Gestiona campañas de WhatsApp y Email</small>
        </div>
        <a href="/{{ $tenant->rut }}/campanas/crear" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Nueva campaña
        </a>
    </div>


    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Canal</th>
                            <th>Segmento</th>
                            <th>Estado</th>
                            <th>Programada</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campanas as $campana)
                            <tr>
                                <td>{{ $campana->titulo }}</td>
                                <td>{{ ucfirst($campana->canal) }}</td>
                                <td>{{ ucfirst($campana->tipo_envio) }}</td>
                                <td>
                                    @php
                                        $badgeColor = match($campana->estado) {
                                            'borrador' => 'secondary',
                                            'completada' => 'success',
                                            'pendiente' => 'warning',
                                            'pausada' => 'secondary',
                                            'en_cola', 'enviando' => 'info',
                                            'fallida' => 'danger',
                                            'cancelada' => 'dark',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }} text-uppercase">
                                        {{ ucfirst($campana->estado) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $fechaProgramada = $campana->fecha_programada;
                                        if (is_string($fechaProgramada)) {
                                            try {
                                                $fechaProgramada = \Illuminate\Support\Carbon::parse($fechaProgramada);
                                            } catch (\Throwable $e) {
                                                $fechaProgramada = null;
                                            }
                                        }
                                    @endphp
                                    @if($fechaProgramada)
                                        {{ $fechaProgramada->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">Inmediato</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="/{{ $tenant->rut }}/campanas/{{ $campana->id }}" class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="visually-hidden">Más acciones</span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if($campana->puedePausarse())
                                                <li>
                                                    <form action="{{ route('tenant.campanas.pause', [$tenant->rut, $campana->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item" onclick="return confirm('¿Pausar esta campaña?')">
                                                            <i class="bi bi-pause-circle me-2"></i>Pausar
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if($campana->puedeReanudarse())
                                                <li>
                                                    <form action="{{ route('tenant.campanas.resume', [$tenant->rut, $campana->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-play-circle me-2"></i>Reanudar
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if($campana->puedeEnviarse())
                                                <li>
                                                    <a href="/{{ $tenant->rut }}/campanas/{{ $campana->id }}" class="dropdown-item">
                                                        <i class="bi bi-send me-2"></i>Revisar y enviar
                                                    </a>
                                                </li>
                                            @endif
                                            @if($campana->puedeEliminarse())
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('tenant.campanas.destroy', [$tenant->rut, $campana->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('¿Eliminar esta campaña?')">
                                                            <i class="bi bi-trash me-2"></i>Eliminar
                                                        </button>
                                                    </form>
                                                </li>
                                            @elseif($campana->puedeArchivarse())
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('tenant.campanas.destroy', [$tenant->rut, $campana->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item" onclick="return confirm('¿Archivar esta campaña?')">
                                                            <i class="bi bi-archive me-2"></i>Archivar
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Aún no registraste campañas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $campanas->links() }}
        </div>
    </div>
</div>
@endsection
