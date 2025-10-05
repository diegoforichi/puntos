<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente No Encontrado - {{ $tenant->nombre_comercial }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .no-encontrado-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .no-encontrado-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .no-encontrado-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .no-encontrado-header i {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        
        .no-encontrado-body {
            padding: 40px 30px;
            text-align: center;
        }
        
        .documento-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            text-align: left;
        }
        
        .info-box h6 {
            color: #f59e0b;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .contact-box {
            background: #e0e7ff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="no-encontrado-container">
        <div class="no-encontrado-card">
            <!-- Header -->
            <div class="no-encontrado-header">
                <i class="bi bi-search"></i>
                <h2 class="mb-2">Cliente No Encontrado</h2>
                <p class="mb-0">{{ $tenant->nombre_comercial }}</p>
            </div>

            <!-- Body -->
            <div class="no-encontrado-body">
                <p class="lead mb-3">
                    No encontramos ningún cliente registrado con este documento:
                </p>
                
                <div class="documento-display">
                    {{ $documento }}
                </div>

                <div class="d-grid mb-3">
                    <a href="/{{ $tenant->rut }}/consulta" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Intentar Nuevamente
                    </a>
                </div>

                <!-- Información -->
                <div class="info-box">
                    <h6>
                        <i class="bi bi-info-circle me-2"></i>
                        ¿Por qué no aparezco?
                    </h6>
                    <p class="mb-0 small">
                        <strong>Aún no te has registrado.</strong> Para comenzar a acumular puntos,
                        realiza tu primera compra en nuestro comercio y proporciona tu documento.
                        A partir de ahí, comenzarás a sumar puntos automáticamente con cada compra.
                    </p>
                </div>

                <div class="info-box mt-3">
                    <h6>
                        <i class="bi bi-gift me-2"></i>
                        ¿Cómo funciona?
                    </h6>
                    <ul class="small text-start mb-0">
                        <li class="mb-2">Realiza tu primera compra y proporciona tu documento</li>
                        <li class="mb-2">Comienza a acumular puntos automáticamente</li>
                        <li class="mb-2">Consulta tus puntos cuando quieras desde aquí</li>
                        <li>Canjea tus puntos por descuentos en tus próximas compras</li>
                    </ul>
                </div>

                <!-- Contacto -->
                @if(!empty($contacto['telefono']) || !empty($contacto['email']) || !empty($contacto['direccion']))
                <div class="contact-box">
                    <h6 class="text-primary mb-3">
                        <i class="bi bi-telephone me-2"></i>
                        ¿Necesitas ayuda? Contáctanos
                    </h6>
                    <p class="mb-0 small">
                        @if(!empty($contacto['telefono']))
                            <i class="bi bi-telephone-fill me-2 text-primary"></i>
                            <strong>Teléfono:</strong> {{ $contacto['telefono'] }}<br>
                        @endif
                        @if(!empty($contacto['email']))
                            <i class="bi bi-envelope-fill me-2 text-primary"></i>
                            <strong>Email:</strong> {{ $contacto['email'] }}<br>
                        @endif
                        @if(!empty($contacto['direccion']))
                            <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
                            <strong>Dirección:</strong> {{ $contacto['direccion'] }}
                        @endif
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
