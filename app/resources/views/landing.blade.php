<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Puntos - Gestión de Fidelización</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .landing-container {
            max-width: 900px;
            margin: 2rem auto;
        }
        
        .hero-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .hero-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .hero-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .feature-box {
            padding: 1.5rem;
            border-left: 4px solid var(--primary-color);
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }
        
        .feature-box:hover {
            transform: translateX(5px);
        }
        
        .feature-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: transform 0.2s;
        }
        
        .btn-primary-custom:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <div class="hero-card">
            <div class="hero-header">
                <div class="hero-icon">
                    <i class="bi bi-award-fill"></i>
                </div>
                <h1 class="display-4 fw-bold mb-3">Sistema de Puntos</h1>
                <p class="lead mb-0">Plataforma de gestión de programas de fidelización para comercios</p>
            </div>
            
            <div class="p-4 p-md-5">
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <h5>Acumulación Automática</h5>
                            <p class="text-muted mb-0">Integración con e-Factura para generar puntos automáticamente por cada compra</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="bi bi-gift"></i>
                            </div>
                            <h5>Canjes Flexibles</h5>
                            <p class="text-muted mb-0">Sistema FIFO para canjear puntos de forma equitativa y transparente</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="bi bi-whatsapp"></i>
                            </div>
                            <h5>Notificaciones WhatsApp</h5>
                            <p class="text-muted mb-0">Mantén a tus clientes informados en tiempo real sobre sus puntos</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="bi bi-tags"></i>
                            </div>
                            <h5>Promociones Dinámicas</h5>
                            <p class="text-muted mb-0">Crea promociones temporales con multiplicadores de puntos</p>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>¿Eres cliente?</strong> Consulta tus puntos acumulados ingresando al portal de tu comercio.
                </div>
                
                <div class="text-center">
                    <a href="/superadmin/login" class="btn btn-primary-custom btn-lg me-2 mb-2">
                        <i class="bi bi-shield-lock me-2"></i>
                        Acceso SuperAdmin
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-lg mb-2" onclick="alert('Ingresa a la URL de tu comercio: tudominio.com/{RUT}/login'); return false;">
                        <i class="bi bi-building me-2"></i>
                        Acceso Comercios
                    </a>
                </div>
                
                <hr class="my-4">
                
                <div class="text-center text-muted small">
                    <p class="mb-1">
                        <i class="bi bi-code-square me-1"></i>
                        Sistema de Puntos v1.0
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-github me-1"></i>
                        Desarrollado con Laravel 10 & PHP 8.2+
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

