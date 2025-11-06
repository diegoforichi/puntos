@extends('layouts.app')

@section('title', 'Detalle de campaña')
@section('page-title', 'Detalle de campaña')
@section('page-subtitle', $tenant->nombre_comercial)

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">{{ $campana->titulo }}</h4>
                <small class="text-muted">{{ ucfirst($campana->canal) }} • {{ ucfirst($campana->tipo_envio) }}</small>
            </div>
            <a href="/{{ $tenant->rut }}/campanas" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Información general</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Estado</dt>
                        <dd class="col-sm-7">
                            @php
                                $badgeColor = match($campana->estado) {
                                    'completada' => 'success',
                                    'pendiente' => 'warning',
                                    'pausada' => 'secondary',
                                    'en_cola', 'enviando' => 'info',
                                    'fallida' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeColor }}">
                                {{ ucfirst($campana->estado) }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Programada</dt>
                        <dd class="col-sm-7">
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
                                <span class="text-muted">Envío inmediato</span>
                            @endif
                        </dd>
                        <dt class="col-sm-5">Creada</dt>
                        <dd class="col-sm-7">{{ $campana->created_at?->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-5">Actualizada</dt>
                        <dd class="col-sm-7">{{ $campana->updated_at?->format('d/m/Y H:i') }}</dd>
                    </dl>

                    <hr>

                    <h6 class="fw-semibold mb-2 text-muted small">Acciones disponibles</h6>
                    <div class="vstack gap-2">
                        @if($campana->puedeProbar())
                            <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#modalEnviarPrueba">
                                <i class="bi bi-send-check me-1"></i>Enviar prueba
                            </button>
                        @endif

                        @if($campana->puedeEnviarse())
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalConfirmarEnvioInmediato">
                                <i class="bi bi-send-fill me-1"></i>Enviar ahora
                            </button>
                        @endif

                        @if($campana->puedePausarse())
                            <form action="{{ route('tenant.campanas.pause', [$tenant->rut, $campana->id]) }}" method="POST" onsubmit="return confirm('¿Pausar esta campaña?');">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="bi bi-pause-circle me-1"></i>Pausar campaña
                                </button>
                            </form>
                        @endif

                        @if($campana->puedeReanudarse())
                            <form action="{{ route('tenant.campanas.resume', [$tenant->rut, $campana->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-play-circle me-1"></i>Reanudar campaña
                                </button>
                            </form>
                        @endif

                        @if($campana->puedeEditarse())
                            <button class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#modalProgramar">
                                <i class="bi bi-clock-history me-1"></i>Cambiar programación
                            </button>
                        @endif

                        @if($campana->puedeEliminarse())
                            <hr class="my-2">
                            <form action="{{ route('tenant.campanas.destroy', [$tenant->rut, $campana->id]) }}" method="POST" onsubmit="return confirm('¿Eliminar esta campaña? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-trash me-1"></i>Eliminar campaña
                                </button>
                            </form>
                        @elseif($campana->puedeArchivarse())
                            <hr class="my-2">
                            <form action="{{ route('tenant.campanas.destroy', [$tenant->rut, $campana->id]) }}" method="POST" onsubmit="return confirm('¿Archivar esta campaña?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-archive me-1"></i>Archivar campaña
                                </button>
                            </form>
                        @endif

                        @if(in_array($campana->estado, ['completada', 'cancelada']))
                            <button type="button" class="btn btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#modalDuplicar">
                                <i class="bi bi-files me-1"></i>Duplicar campaña
                            </button>
                        @endif

                        @if(!$campana->puedeEnviarse() && !$campana->puedePausarse() && !$campana->puedeReanudarse() && !$campana->puedeEliminarse())
                            <div class="alert alert-info mb-0 small">
                                <i class="bi bi-info-circle me-1"></i>
                                No hay acciones disponibles para esta campaña en su estado actual.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-journal-text me-2 text-success"></i>Resumen de envíos</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <div class="fw-semibold text-muted">Programados</div>
                                <div class="fs-4">{{ $conteoEnvios['programados'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <div class="fw-semibold text-muted">Enviados</div>
                                <div class="fs-4 text-success">{{ $conteoEnvios['enviados'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <div class="fw-semibold text-muted">Fallidos</div>
                                <div class="fs-4 text-danger">{{ $conteoEnvios['fallidos'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <div class="fw-semibold text-muted">Canal</div>
                                <div class="fs-4">{{ ucfirst($campana->canal) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="fw-semibold"><i class="bi bi-list-check me-2 text-secondary"></i>Últimos envíos</div>
                    <small class="text-muted">Mostrando 50 recientes</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th>Intentos</th>
                                    <th>Fecha envío</th>
                                    <th>Mensaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campana->envios as $envio)
                                    <tr>
                                        <td>#{{ $envio->cliente_id }}</td>
                                        <td>
                                            <span class="badge bg-{{ $envio->estado === 'enviado' ? 'success' : ($envio->estado === 'pendiente' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($envio->estado) }}
                                            </span>
                                        </td>
                                        <td>{{ $envio->intentos }}</td>
                                        <td>
                                            @php
                                                $sentAt = $envio->sent_at ?? null;
                                                if (is_string($sentAt)) {
                                                    try { $sentAt = \Illuminate\Support\Carbon::parse($sentAt); } catch (\Throwable $e) { $sentAt = null; }
                                                }
                                            @endphp
                                            {{ $sentAt ? $sentAt->format('d/m/Y H:i') : '—' }}
                                        </td>
                                        <td class="text-muted small">
                                            {{ Str::limit($envio->error_mensaje, 60) ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Aún no hay envíos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal enviar prueba -->
<div class="modal fade" id="modalEnviarPrueba" tabindex="-1" aria-labelledby="modalEnviarPruebaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tenant.campanas.send-test', [$tenant->rut, $campana->id]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEnviarPruebaLabel">Enviar prueba</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Envía una prueba antes de distribuir la campaña.
                        Completa los datos de contacto donde quieras recibirla.</p>

                    @error('prueba')
                        <div class="alert alert-danger small">{{ $message }}</div>
                    @enderror

                    @php $canalesPrueba = $campana->canales(); @endphp

                    @if(in_array('whatsapp', $canalesPrueba, true))
                        <div class="mb-3">
                            <label class="form-label">Teléfono (WhatsApp)</label>
                            <input type="text" name="telefono_prueba" class="form-control" value="{{ old('telefono_prueba') }}" placeholder="Ej: +59899123456">
                            <div class="form-text">Si lo dejas vacío, no se enviará la prueba por WhatsApp.</div>
                            @error('telefono_prueba')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if(in_array('email', $canalesPrueba, true))
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email_prueba" class="form-control" value="{{ old('email_prueba') }}" placeholder="correo@ejemplo.com">
                            <div class="form-text">Si lo dejas vacío, no se enviará la prueba por email.</div>
                            @error('email_prueba')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <p class="small text-muted mb-0">Los mensajes de prueba no afectan los totales de la campaña.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar prueba</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal confirmar envío inmediato -->
<div class="modal fade" id="modalConfirmarEnvioInmediato" tabindex="-1" aria-labelledby="modalConfirmarEnvioInmediatoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalConfirmarEnvioInmediatoLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirmar envío inmediato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small">
                    <strong>Atención:</strong> El envío comenzará inmediatamente y <strong>no podrás cancelarlo</strong>.
                    Se procesarán {{ $conteoEnvios['programados'] ?? 0 }} envíos.
                </div>

                <h6 class="fw-semibold mb-3">Antes de continuar, confirma:</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="confirmEnvio1" required>
                    <label class="form-check-label" for="confirmEnvio1">Revisé el contenido del mensaje.</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="confirmEnvio2" required>
                    <label class="form-check-label" for="confirmEnvio2">Envié y recibí correctamente la prueba.</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="confirmEnvio3" required>
                    <label class="form-check-label" for="confirmEnvio3">Entiendo que el envío no puede cancelarse.</label>
                </div>

                <div class="bg-light rounded p-3 small">
                    <strong>Resumen:</strong>
                    <ul class="mb-0">
                        <li>Canal: {{ ucfirst($campana->canal) }}</li>
                        <li>Destinatarios: {{ $conteoEnvios['destinatarios'] ?? 0 }}</li>
                        <li>Segmento: {{ ucfirst($campana->tipo_envio) }}</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarEnvio" disabled>
                    <i class="bi bi-send-fill me-1"></i>Enviar ahora
                </button>
            </div>
        </div>
    </div>
</div>

<form id="formEnviarAhora" action="{{ route('tenant.campanas.send', [$tenant->rut, $campana->id]) }}" method="POST" class="d-none">
    @csrf
</form>

<!-- Modal duplicar -->
<div class="modal fade" id="modalDuplicar" tabindex="-1" aria-labelledby="modalDuplicarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tenant.campanas.duplicate', [$tenant->rut, $campana->id]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDuplicarLabel">Duplicar campaña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Se creará una copia de esta campaña como <strong>Borrador</strong> con todo el contenido.</p>
                    <div class="mb-3">
                        <label class="form-label">Nuevo nombre (opcional)</label>
                        <input type="text" name="titulo_duplicado" class="form-control" placeholder="{{ $campana->titulo }} (Copia)" value="{{ $campana->titulo }} (Copia)">
                        <div class="form-text">Si lo dejas vacío, se agregará "(Copia)" al nombre original.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Duplicar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal programar -->
<div class="modal fade" id="modalProgramar" tabindex="-1" aria-labelledby="modalProgramarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/{{ $tenant->rut }}/campanas/{{ $campana->id }}/programar" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProgramarLabel">Programar campaña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        La campaña se enviará <strong>automáticamente</strong> en la fecha y hora seleccionadas, incluso si nadie está conectado.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha_programada" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hora</label>
                        <input type="time" name="hora_programada" class="form-control" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmProgramacion" required>
                        <label class="form-check-label" for="confirmProgramacion">Entiendo que la campaña se enviará automáticamente en la fecha indicada.</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar programación</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('mostrar_modal_prueba'))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modalEl = document.getElementById('modalEnviarPrueba');
                if (modalEl) {
                    var modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            });
        </script>
    @endpush
@endif
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const confirmChecks = document.querySelectorAll('#modalConfirmarEnvioInmediato input[type="checkbox"]');
            const btnConfirmarEnvio = document.getElementById('btnConfirmarEnvio');
            const formEnviarAhora = document.getElementById('formEnviarAhora');

            function toggleConfirmButton() {
                if (!btnConfirmarEnvio) {
                    return;
                }

                const todosMarcados = Array.from(confirmChecks).every((check) => check.checked);
                btnConfirmarEnvio.disabled = !todosMarcados;
            }

            confirmChecks.forEach((check) => check.addEventListener('change', toggleConfirmButton));

            btnConfirmarEnvio?.addEventListener('click', function () {
                formEnviarAhora?.submit();
            });
        });
    </script>
@endpush
@endsection
