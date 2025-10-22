@extends('layouts.plain')

@section('content')
<div class="consulta-container" style="--tenant-color-primary: {{ $tema['primario'] ?? '#2563eb' }}; --tenant-color-primary-light: {{ $tema['primario_claro'] ?? '#3f83f8' }}; --tenant-color-secondary: {{ $tema['secundario'] ?? '#64748b' }};">
    <div class="consulta-card">
        <div class="consulta-header text-center">
            <h2 class="mb-1">Consulta tus Puntos</h2>
            <p class="mb-0 text-muted" style="font-size: 0.95rem;">{{ $tenant->nombre_comercial }}</p>
        </div>

        <div class="consulta-body">
            <h5 class="mb-3 text-center">Ingresa tu documento para consultar</h5>

            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('info') }}
                </div>
            @endif

            <form action="/{{ $tenant->rut }}/consulta" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="documento" class="form-label">
                        <i class="bi bi-person-badge me-2"></i>
                        Número de Documento
                    </label>
                    <input
                        type="text"
                        class="form-control @error('documento') is-invalid @enderror"
                        id="documento"
                        name="documento"
                        placeholder="Ingresa tu documento"
                        value="{{ old('documento', $documento) }}"
                        required
                        autofocus
                    >
                    @error('documento')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted mt-2">
                        Ingresa tu cédula sin puntos ni guiones
                    </small>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>
                        Consultar Puntos
                    </button>
                </div>
            </form>

            @if($resultado && $resultado['encontrado'] && $detalle)
                <hr class="my-4">

                <div class="mb-3 text-center">
                    <h5 class="text-primary mb-2">Hola {{ $detalle['cliente']->nombre ?? 'cliente' }}</h5>
                    <div class="consulta-saldo">
                        <small class="text-uppercase text-muted" style="letter-spacing: .1em;">Puntos disponibles</small>
                <div class="consulta-saldo-val" style="font-size:3.75rem;font-weight:800;color:var(--tenant-color-secondary);">
                    {{ $detalle['stats']['puntos_formateados'] }}
                </div>
                    </div>
                    <p class="mb-3 text-muted">
                        Próxima expiración:
                        <strong>
                            {{ (isset($detalle['stats']['proxima_expiracion']) && $detalle['stats']['proxima_expiracion']) ? $detalle['stats']['proxima_expiracion']->format('d/m/Y') : '—' }}
                        </strong>
                    </p>
                </div>

                @if($detalle['mensaje'])
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>{{ $detalle['mensaje'] }}
                    </div>
                @endif

                @if(count($detalle['facturas']))
                    <div class="info-box">
                        <button class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#facturasDetalle" aria-expanded="true" aria-controls="facturasDetalle">
                            <span><i class="bi bi-receipt me-2"></i>Facturas con puntos disponibles</span>
                            <span class="badge bg-primary rounded-pill">{{ count($detalle['facturas']) }}</span>
                        </button>
                        <div class="collapse show" id="facturasDetalle">
                            <ul class="small mb-0">
                                @foreach($detalle['facturas'] as $factura)
                                    <li class="mb-1">
                                        <strong>{{ $factura->fecha_emision->format('d/m/Y') }}</strong>
                                        • {{ number_format($factura->puntos_generados, 2, ',', '.') }} pts
                                        • Vence: {{ $factura->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="alert alert-light text-center">
                        <i class="bi bi-emoji-smile me-2"></i>
                        No tienes facturas activas en este momento. ¡Sigue acumulando puntos!
                    </div>
                @endif

                <div class="info-box mt-3">
                    <h6>
                        <i class="bi bi-envelope-paper me-2"></i>
                        ¿Quieres actualizar tus datos?
                    </h6>
                    <form action="/{{ $tenant->rut }}/consulta/actualizar-contacto" method="POST" class="row g-2">
                        @csrf
                        <input type="hidden" name="cliente_id" value="{{ $detalle['cliente']->id }}">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" value="{{ $detalle['cliente']->telefono }}">
                                <label for="telefono">Teléfono</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Correo" value="{{ $detalle['cliente']->email }}">
                                <label for="email">Email</label>
                            </div>
                        </div>
                        <div class="col-12">
                        @php($colorSec = $tema['secundario'] ?? '#64748b')
                        <button type="submit" class="btn btn-outline-primary w-100" style="border-color: {{ $colorSec }}; color: {{ $colorSec }};">
                                <i class="bi bi-save me-2"></i>
                                Actualizar datos de contacto
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="accordion mt-4" id="consultaFAQ">
                <div class="accordion-item" style="border:1px solid rgba(0,0,0,.05);">
                    <h6 class="accordion-header" id="faqHeading">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse" aria-expanded="false" aria-controls="faqCollapse">
                            <i class="bi bi-info-circle me-2"></i>¿Para qué sirven los puntos?
                        </button>
                    </h6>
                    <div id="faqCollapse" class="accordion-collapse collapse" aria-labelledby="faqHeading" data-bs-parent="#consultaFAQ">
                        <div class="accordion-body small">
                            Acumulas puntos con cada compra y luego puedes canjearlos por descuentos en {{ $tenant->nombre_comercial }}.
                            ¡Mientras más compras, más puntos acumulas!
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($contacto['telefono']) || !empty($contacto['email']) || !empty($contacto['direccion']))
            <div class="info-box mt-3">
                <h6>
                    <i class="bi bi-telephone me-2"></i>
                    ¿Necesitas ayuda?
                </h6>
                <p class="mb-0 small">
                    @if(!empty($contacto['telefono']))
                        <strong>Teléfono:</strong> {{ $contacto['telefono'] }}<br>
                    @endif
                    @if(!empty($contacto['email']))
                        <strong>Email:</strong> {{ $contacto['email'] }}<br>
                    @endif
                    @if(!empty($contacto['direccion']))
                        <strong>Dirección:</strong> {{ $contacto['direccion'] }}
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>

    <div class="footer-link">
        <a href="/{{ $tenant->rut }}/login">
            <i class="bi bi-lock me-1"></i>
            Acceso para empleados
        </a>
    </div>
</div>
@endsection
