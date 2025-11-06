@extends('layouts.app')

@section('title', 'Nueva Campaña')
@section('page-title', 'Crear campaña')
@section('page-subtitle', $tenant->nombre_comercial)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-megaphone me-2 text-primary"></i>Nueva campaña de difusión</h5>
                        <small class="text-muted">Configura el contenido y el canal a utilizar.</small>
                    </div>
                    <a href="/{{ $tenant->rut }}/campanas" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
                </div>
                <div class="card-body">
                    <form action="/{{ $tenant->rut }}/campanas" method="POST" class="vstack gap-4">
                        @csrf

                        <div>
                            <label class="form-label">Nombre interno</label>
                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Canal</label>
                                <select name="canal" class="form-select @error('canal') is-invalid @enderror" required>
                                    <option value="whatsapp" {{ old('canal') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                    <option value="email" {{ old('canal') === 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="ambos" {{ old('canal') === 'ambos' ? 'selected' : '' }}>WhatsApp y Email</option>
                                </select>
                                @error('canal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Segmento destinatarios</label>
                                <select name="segmento" class="form-select @error('segmento') is-invalid @enderror" required>
                                    <option value="todos" {{ old('segmento') === 'todos' ? 'selected' : '' }}>Todos los clientes</option>
                                    <option value="activos" {{ old('segmento') === 'activos' ? 'selected' : '' }}>Clientes activos</option>
                                    <option value="inactivos" {{ old('segmento') === 'inactivos' ? 'selected' : '' }}>Clientes inactivos (90 días)</option>
                                </select>
                                @error('segmento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="border rounded p-3">
                            <h6 class="fw-semibold"><i class="bi bi-whatsapp me-2 text-success"></i>Contenido para WhatsApp</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="text-muted small mb-0">Máximo 200 caracteres. Soporta *negritas* y _cursivas_. Usa {{ '{nombre}' }} y {{ '{puntos}' }}. Si el canal WhatsApp está deshabilitado no se enviará.</p>
                                <small class="text-muted" id="whatsappCounter">0 / 200</small>
                            </div>
                            <textarea name="mensaje_whatsapp" id="mensajeWhatsapp" class="form-control @error('mensaje_whatsapp') is-invalid @enderror" rows="4" maxlength="200" placeholder="Saludos {{ '{nombre}' }}, te invitamos a sumar más puntos esta semana.">{{ old('mensaje_whatsapp') }}</textarea>
                            @error('mensaje_whatsapp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="border rounded p-3">
                            <h6 class="fw-semibold"><i class="bi bi-envelope-open me-2 text-primary"></i>Contenido para Email</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Asunto</label>
                                    <input type="text" name="asunto_email" class="form-control @error('asunto_email') is-invalid @enderror" value="{{ old('asunto_email') }}" placeholder="Ingresa el asunto del correo">
                                    @error('asunto_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Título</label>
                                    <input type="text" name="titulo_email" class="form-control @error('titulo_email') is-invalid @enderror" value="{{ old('titulo_email') }}">
                                    @error('titulo_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Subtítulo</label>
                                    <input type="text" name="subtitulo_email" class="form-control @error('subtitulo_email') is-invalid @enderror" value="{{ old('subtitulo_email') }}">
                                    @error('subtitulo_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Imagen (URL)</label>
                                    <input type="url" name="imagen_email" class="form-control @error('imagen_email') is-invalid @enderror" value="{{ old('imagen_email') }}" placeholder="https://...">
                                    @error('imagen_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Texto principal</label>
                                    <textarea name="texto_email" class="form-control @error('texto_email') is-invalid @enderror" rows="6" placeholder="Escribe el contenido principal del correo. Puedes usar {{ '{nombre}' }} y {{ '{puntos}' }}.">{{ old('texto_email') }}</textarea>
                                    @error('texto_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border rounded p-3">
                            <h6 class="fw-semibold"><i class="bi bi-clock-history me-2 text-warning"></i>Programación</h6>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="programarCampana" name="programar" value="1" {{ old('programar') ? 'checked' : '' }}>
                                <label class="form-check-label" for="programarCampana">Programar envío en una fecha/hora específica</label>
                            </div>
                            <div class="row g-3 align-items-end {{ old('programar') ? '' : 'd-none' }}" id="programacionCampos">
                                <div class="col-md-6">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" name="fecha_programada" class="form-control @error('fecha_programada') is-invalid @enderror" value="{{ old('fecha_programada') }}">
                                    @error('fecha_programada')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Hora</label>
                                    <input type="time" name="hora_programada" class="form-control @error('hora_programada') is-invalid @enderror" value="{{ old('hora_programada') }}">
                                    @error('hora_programada')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="enviarInmediato" name="enviar_inmediato" value="1" {{ old('enviar_inmediato') ? 'checked' : '' }}>
                            <label class="form-check-label" for="enviarInmediato">
                                Enviar inmediatamente al guardar (si no, se guarda como Borrador)
                            </label>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/{{ $tenant->rut }}/campanas" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Guardar campaña
                            </button>
                        </div>
                        <div class="alert alert-info small d-flex align-items-start mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                Si no marcas "Enviar inmediatamente", la campaña se guardará como <strong>Borrador</strong> y podrás enviarla más tarde desde el panel.
                            </div>
                        </div>
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
            whatsappCounter.textContent = `${longitud} / 200`;

            whatsappCounter.classList.toggle('text-danger', longitud > 200);
        }

        whatsappField?.addEventListener('input', actualizarContadorWhatsapp);
        actualizarContadorWhatsapp();
    });
</script>
@endpush
