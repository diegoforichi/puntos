<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tus Puntos - {{ $tenant->nombre_comercial }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .resultado-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .resultado-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .resultado-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .puntos-display {
            font-size: 4rem;
            font-weight: bold;
            margin: 20px 0;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.5s;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .cliente-info {
            padding: 30px;
        }
        
        .stat-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .stat-box h3 {
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-box p {
            color: #6b7280;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .alert-warning-custom {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 10px;
        }
        
        .btn-back {
            background: white;
            color: #667eea;
            border: 2px solid white;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: transparent;
            color: white;
            transform: translateY(-2px);
        }
        
        .contacto-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .badge {
            font-size: 0.85rem;
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <div class="resultado-container">
        <!-- Card de Puntos -->
        <div class="resultado-card">
            <div class="resultado-header">
                <h4 class="mb-0">
                    <i class="bi bi-person-circle me-2"></i>
                    {{ $cliente->nombre }}
                </h4>
                <small>Documento: {{ $cliente->documento }}</small>
                
                <div class="puntos-display">
                    <i class="bi bi-trophy-fill me-3"></i>
                    {{ $stats['puntos_formateados'] }}
                </div>
                
                <p class="mb-0">PUNTOS DISPONIBLES</p>
                
                <a href="/{{ $tenant->rut }}/consulta" class="btn btn-back mt-4">
                    <i class="bi bi-arrow-left me-2"></i>
                    Nueva Consulta
                </a>
            </div>

            <div class="cliente-info">
                <!-- Alertas -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($stats['facturas_por_vencer'] > 0)
                <div class="alert-warning-custom mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>¡Atención!</strong> Tienes {{ $stats['facturas_por_vencer'] }} factura(s) con puntos que vencerán en los próximos 30 días.
                    ¡No olvides canjearlos!
                </div>
                @endif

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-box">
                            <h3>{{ $stats['total_facturas'] }}</h3>
                            <p>Facturas Activas</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <h3>{{ number_format($stats['puntos_generados_total'], 0) }}</h3>
                            <p>Puntos Generados</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <h3>{{ number_format($stats['puntos_canjeados_total'], 0) }}</h3>
                            <p>Puntos Canjeados</p>
                        </div>
                    </div>
                </div>

                <!-- Facturas Activas -->
                @if($facturasActivas->count() > 0)
                <h5 class="mb-3">
                    <i class="bi bi-receipt me-2"></i>
                    Detalle de tus Puntos
                </h5>
                <div class="table-responsive mb-4">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Factura</th>
                                <th class="text-end">Puntos</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($facturasActivas as $factura)
                            <tr>
                                <td>
                                    <small><code>{{ $factura->numero_factura }}</code></small>
                                </td>
                                <td class="text-end">
                                    <strong>{{ $factura->puntos_formateados }}</strong>
                                </td>
                                <td>
                                    <small>{{ $factura->fecha_vencimiento->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $factura->badge_estado['class'] }}">
                                        {{ $factura->badge_estado['text'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Información de Canje -->
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>¿Cómo canjear tus puntos?</strong><br>
                    <small>
                        Acércate a cualquiera de nuestras tiendas y presenta tu documento.
                        Nuestro personal te ayudará a usar tus puntos como descuento en tu próxima compra.
                    </small>
                </div>

                <!-- Formulario de Contacto (solo si no tiene datos) -->
                @if(!$cliente->telefono || !$cliente->email)
                <div class="contacto-form">
                    <h6 class="mb-3">
                        <i class="bi bi-envelope me-2"></i>
                        Déjanos tus datos de contacto (opcional)
                    </h6>
                    <p class="small text-muted mb-3">
                        Para enviarte notificaciones cuando tus puntos estén por vencer
                    </p>
                    
                    <form action="/{{ $tenant->rut }}/consulta/actualizar-contacto" method="POST">
                        @csrf
                        <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                        
                        <div class="row">
                            @if(!$cliente->telefono)
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label small">Teléfono</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="telefono" 
                                    name="telefono"
                                    placeholder="099123456"
                                >
                            </div>
                            @endif
                            
                            @if(!$cliente->email)
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label small">Email</label>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    id="email" 
                                    name="email"
                                    placeholder="tu@email.com"
                                >
                            </div>
                            @endif
                        </div>
                        
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Guardar Contacto
                        </button>
                    </form>
                </div>
                @endif

                <!-- Contacto del Comercio -->
                @if(!empty($contacto['telefono']) || !empty($contacto['email']) || !empty($contacto['direccion']))
                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="mb-2">
                        <i class="bi bi-shop me-2"></i>
                        Información de Contacto
                    </h6>
                    <small>
                        @if(!empty($contacto['telefono']))
                            <i class="bi bi-telephone me-1"></i> {{ $contacto['telefono'] }}<br>
                        @endif
                        @if(!empty($contacto['email']))
                            <i class="bi bi-envelope me-1"></i> {{ $contacto['email'] }}<br>
                        @endif
                        @if(!empty($contacto['direccion']))
                            <i class="bi bi-geo-alt me-1"></i> {{ $contacto['direccion'] }}
                        @endif
                    </small>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
