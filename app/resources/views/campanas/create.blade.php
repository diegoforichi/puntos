@extends('layouts.app')

@php
    $isEdit = isset($campana);
    $formAction = $isEdit
        ? route('tenant.campanas.update', [$tenant->rut, $campana->id])
        : route('tenant.campanas.store', $tenant->rut);

    $tituloPagina = $isEdit ? 'Editar campaña' : 'Crear campaña';

    $nombreValor = old('nombre', $isEdit ? $campana->titulo : '');
    $segmentoValor = old('segmento', $isEdit ? $campana->tipo_envio : 'todos');
    $canalValor = old('canal', $isEdit ? $campana->canal : 'whatsapp');
    $mensajeWhatsappValor = old('mensaje_whatsapp', $isEdit ? $campana->mensaje_whatsapp : '');
    $asuntoEmailValor = old('asunto_email', $isEdit ? $campana->asunto_email : '');
    $tituloEmailValor = old('titulo_email', $isEdit ? ($campana->titulo_email ?? $campana->titulo) : '');
    $subtituloEmailValor = old('subtitulo_email', $isEdit ? $campana->subtitulo : '');
    $imagenEmailValor = old('imagen_email', $isEdit ? $campana->imagen_url : '');
    $textoEmailValor = old('texto_email', $isEdit ? $campana->cuerpo_texto : '');
    $programarChecked = old('programar', $isEdit && $campana->fecha_programada ? 1 : null);
    $fechaProgramadaValor = old(
        'fecha_programada',
        $isEdit && $campana->fecha_programada ? $campana->fecha_programada->setTimezone('America/Montevideo')->format('Y-m-d') : null
    );
    $horaProgramadaValor = old(
        'hora_programada',
        $isEdit && $campana->fecha_programada ? $campana->fecha_programada->setTimezone('America/Montevideo')->format('H:i') : null
    );
@endphp

@section('title', $isEdit ? 'Editar Campaña' : 'Nueva Campaña')
@section('page-title', $tituloPagina)
@section('page-subtitle', $tenant->nombre_comercial)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">
                            <i class="bi bi-megaphone me-2 text-primary"></i>{{ $tituloPagina }}
                        </h5>
                        <small class="text-muted">
                            {{ $isEdit ? 'Actualiza el contenido antes de enviarla.' : 'Configura el contenido y el canal a utilizar.' }}
                        </small>
                    </div>
                    <a href="/{{ $tenant->rut }}/campanas" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
                </div>
                <div class="card-body">
                    <form action="{{ $formAction }}" method="POST" class="vstack gap-4">
                        @csrf
                        @if($isEdit)
                            @method('PUT')
                        @endif

                        <div>
                            <label class="form-label">Nombre interno</label>
                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ $nombreValor }}" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Canal</label>
                                <select name="canal" class="form-select @error('canal') is-invalid @enderror" required>
                                    <option value="whatsapp" {{ $canalValor === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                    <option value="email" {{ $canalValor === 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="ambos" {{ $canalValor === 'ambos' ? 'selected' : '' }}>WhatsApp y Email</option>
                                </select>
                                @error('canal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Segmento destinatarios</label>
                                <select name="segmento" class="form-select @error('segmento') is-invalid @enderror" required>
                                    <option value="todos" {{ $segmentoValor === 'todos' ? 'selected' : '' }}>Todos los clientes</option>
                                    <option value="activos" {{ $segmentoValor === 'activos' ? 'selected' : '' }}>Clientes activos</option>
                                    <option value="inactivos" {{ $segmentoValor === 'inactivos' ? 'selected' : '' }}>Clientes inactivos (90 días)</option>
                                </select>
                                @error('segmento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="border rounded p-3">
                            <h6 class="fw-semibold"><i class="bi bi-whatsapp me-2 text-success"></i>Contenido para WhatsApp</h6>
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                                <p class="text-muted small mb-0">Máximo 700 caracteres. Soporta *negritas* y _cursivas_. Usa {{ '{nombre}' }} y {{ '{puntos}' }}. Si el canal WhatsApp está deshabilitado no se enviará.</p>
                                <div class="d-flex align-items-center gap-3">
                                    <small class="text-muted" id="whatsappCounter">0 / 700</small>
                                    <small class="text-muted" id="whatsappBytesCounter">0 / 950 bytes</small>
                                </div>
                            </div>
                            <textarea name="mensaje_whatsapp" id="mensajeWhatsapp" class="form-control @error('mensaje_whatsapp') is-invalid @enderror" rows="4" maxlength="700" placeholder="Saludos {{ '{nombre}' }}, te invitamos a sumar más puntos esta semana.">{{ $mensajeWhatsappValor }}</textarea>
                            @error('mensaje_whatsapp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="border rounded p-3">
                            <h6 class="fw-semibold"><i class="bi bi-envelope-open me-2 text-primary"></i>Contenido para Email</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Asunto</label>
                                    <input type="text" name="asunto_email" class="form-control @error('asunto_email') is-invalid @enderror" value="{{ $asuntoEmailValor }}" placeholder="Ingresa el asunto del correo">
                                    @error('asunto_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Título</label>
                                    <input type="text" name="titulo_email" class="form-control @error('titulo_email') is-invalid @enderror" value="{{ $tituloEmailValor }}">
                                    @error('titulo_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Subtítulo</label>
                                    <input type="text" name="subtitulo_email" class="form-control @error('subtitulo_email') is-invalid @enderror" value="{{ $subtituloEmailValor }}">
                                    @error('subtitulo_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Imagen (URL)</label>
                                    <input type="url" name="imagen_email" class="form-control @error('imagen_email') is-invalid @enderror" value="{{ $imagenEmailValor }}" placeholder="https://...">
                                    @error('imagen_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Texto principal</label>
                                    <textarea name="texto_email" class="form-control @error('texto_email') is-invalid @enderror" rows="6" placeholder="Escribe el contenido principal del correo. Puedes usar {{ '{nombre}' }} y {{ '{puntos}' }}.">{{ $textoEmailValor }}</textarea>
                                    @error('texto_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border rounded p-3">
                            <h6 class="fw-semibold"><i class="bi bi-clock-history me-2 text-warning"></i>Programación</h6>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="programarCampana" name="programar" value="1" {{ $programarChecked ? 'checked' : '' }}>
                                <label class="form-check-label" for="programarCampana">Programar envío en una fecha/hora específica</label>
                            </div>
                            <div class="row g-3 align-items-end {{ $programarChecked ? '' : 'd-none' }}" id="programacionCampos">
                                <div class="col-md-6">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" name="fecha_programada" class="form-control @error('fecha_programada') is-invalid @enderror" value="{{ $fechaProgramadaValor }}">
                                    @error('fecha_programada')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Hora</label>
                                    <input type="time" name="hora_programada" class="form-control @error('hora_programada') is-invalid @enderror" value="{{ $horaProgramadaValor }}">
                                    @error('hora_programada')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @unless($isEdit)
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="enviarInmediato" name="enviar_inmediato" value="1" {{ old('enviar_inmediato') ? 'checked' : '' }}>
                                <label class="form-check-label" for="enviarInmediato">
                                    Enviar inmediatamente al guardar (si no, se guarda como Borrador)
                                </label>
                            </div>
                        @endunless

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/{{ $tenant->rut }}/campanas" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ $isEdit ? 'Guardar cambios' : 'Guardar campaña' }}
                            </button>
                        </div>
                        @unless($isEdit)
                            <div class="alert alert-info small d-flex align-items-start mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <div>
                                    Si no marcas "Enviar inmediatamente", la campaña se guardará como <strong>Borrador</strong> y podrás enviarla más tarde desde el panel.
                                </div>
                            </div>
                        @endunless
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const programarSwitch = document.getElementById('programarCampana');
        const programacionCampos = document.getElementById('programacionCampos');
        const whatsappField = document.getElementById('mensajeWhatsapp');
        const whatsappCounter = document.getElementById('whatsappCounter');
        const whatsappBytesCounter = document.getElementById('whatsappBytesCounter');
        const whatsappBytesLimit = 950;

        function toggleProgramacion() {
            if (!programacionCampos) {
                return;
            }

            if (programarSwitch.checked) {
                programacionCampos.classList.remove('d-none');
            } else {
                programacionCampos.classList.add('d-none');
            }
        }

        programarSwitch?.addEventListener('change', toggleProgramacion);
        programarSwitch && toggleProgramacion();

        function actualizarContadorWhatsapp() {
            if (!whatsappField || !whatsappCounter) {
                return;
            }

            const longitud = whatsappField.value.length;
            const max = whatsappField.maxLength || 700;
            const bytes = encodeURIComponent(whatsappField.value).length;

            whatsappCounter.textContent = `${longitud} / ${max}`;

            whatsappCounter.classList.toggle('text-danger', longitud > max);

            if (whatsappBytesCounter) {
                whatsappBytesCounter.textContent = `${bytes} / ${whatsappBytesLimit} bytes`;
                whatsappBytesCounter.classList.remove('text-muted', 'text-warning', 'text-danger', 'fw-semibold');

                if (bytes > whatsappBytesLimit) {
                    whatsappBytesCounter.classList.add('text-danger', 'fw-semibold');
                } else if (bytes > whatsappBytesLimit - 100) {
                    whatsappBytesCounter.classList.add('text-warning');
                } else {
                    whatsappBytesCounter.classList.add('text-muted');
                }
            }
        }

        whatsappField?.addEventListener('input', actualizarContadorWhatsapp);
        actualizarContadorWhatsapp();
    });
</script>
@endpush
