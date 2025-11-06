@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0">Nuevo Cliente</h1>
            <p class="text-muted mb-0">Registra un cliente sin necesidad de facturación previa.</p>
        </div>
        <a href="/{{ $tenant->rut }}/clientes" class="btn btn-light border">
            <i class="bi bi-arrow-left"></i>
            Volver al listado
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="/{{ $tenant->rut }}/clientes">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Documento *</label>
                                <input type="text" name="documento" class="form-control @error('documento') is-invalid @enderror" value="{{ old('documento') }}" required>
                                @error('documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre *</label>
                                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required>
                                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono') }}">
                                @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Dirección</label>
                                <textarea name="direccion" class="form-control @error('direccion') is-invalid @enderror" rows="2">{{ old('direccion') }}</textarea>
                                @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Puntos iniciales</label>
                                <input type="number" name="puntos_iniciales" class="form-control @error('puntos_iniciales') is-invalid @enderror" value="{{ old('puntos_iniciales', 0) }}" min="0" step="0.01">
                                @error('puntos_iniciales')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="/{{ $tenant->rut }}/clientes" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title fw-semibold">Sugerencias</h5>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>Usa el documento como identificador único.</li>
                        <li>Los puntos iniciales permiten cargar saldos previos.</li>
                        <li>Puedes modificar más detalles desde la edición del cliente.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
