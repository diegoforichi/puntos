@extends('superadmin.layout')

@section('title', 'Configuración Global')
@section('page-title', 'Configuración del Sistema')
@section('page-subtitle', 'Email SMTP y WhatsApp centralizados')

@section('content')
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>Configuración Email SMTP</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.config.email') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Servidor SMTP</label>
                        <input type="text" name="smtp_host" class="form-control" value="{{ $emailConfig['smtp_host'] ?? '' }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Puerto</label>
                            <input type="number" name="smtp_port" class="form-control" value="{{ $emailConfig['smtp_port'] ?? 587 }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="smtp_user" class="form-control" value="{{ $emailConfig['smtp_user'] ?? '' }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cifrado</label>
                            <select name="smtp_encryption" class="form-select">
                                @foreach(\App\Models\SystemConfig::getSmtpEncryption() as $label => $value)
                                    <option value="{{ $value }}" {{ ($emailConfig['smtp_encryption'] ?? null) === $value ? 'selected' : '' }}>
                                        {{ strtoupper($label) ?: 'SIN CIFRADO' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="smtp_pass" class="form-control" placeholder="(sin cambios)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remitente</label>
                        <input type="email" name="from_address" class="form-control" value="{{ $emailConfig['from_address'] ?? '' }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Nombre Remitente</label>
                        <input type="text" name="from_name" class="form-control" value="{{ $emailConfig['from_name'] ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTestEmail">
                            <i class="bi bi-send"></i> Enviar email de prueba
                        </button>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar Configuración
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-whatsapp me-2"></i>Configuración WhatsApp</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.config.whatsapp') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Endpoint / URL</label>
                        <input type="url" name="url" class="form-control" value="{{ $whatsappConfig['url'] ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Token</label>
                        <input type="password" name="token" class="form-control" placeholder="(sin cambios)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Código País</label>
                        <input type="text" name="codigo_pais" class="form-control" value="{{ $whatsappConfig['codigo_pais'] ?? '+598' }}" required>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" {{ ($whatsappConfig['activo'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Servicio habilitado</label>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTestWhatsapp">
                            <i class="bi bi-send"></i> Enviar WhatsApp de prueba
                        </button>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar Configuración
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Email Prueba -->
<div class="modal fade" id="modalTestEmail" tabindex="-1" aria-labelledby="modalTestEmailLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="form-test-email" method="POST" action="{{ route('superadmin.config.email.test') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalTestEmailLabel"><i class="bi bi-send"></i> Enviar email de prueba</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Enviar a</label>
                    <input type="email" name="email" class="form-control" value="{{ auth()->user()->email ?? '' }}" required>
                    <small class="text-muted">Correo que recibirá el mensaje de prueba.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i> Enviar prueba
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal WhatsApp Prueba -->
<div class="modal fade" id="modalTestWhatsapp" tabindex="-1" aria-labelledby="modalTestWhatsappLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="form-test-whatsapp" method="POST" action="{{ route('superadmin.config.whatsapp.test') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalTestWhatsappLabel"><i class="bi bi-send"></i> Enviar WhatsApp de prueba</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Número de teléfono</label>
                    <input type="tel" name="telefono" class="form-control" placeholder="+59899123456" required>
                    <small class="text-muted">Ingresa el número completo con código de país, sin espacios ni guiones.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-send"></i> Enviar prueba
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
