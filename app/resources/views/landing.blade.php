<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Puntos - Plataforma de Fidelización para Comercios</title>
    <meta name="description" content="Plataforma integral de gestión de programas de fidelización con integración automática vía e-Factura, notificaciones WhatsApp y reportes completos.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --accent-color: #10b981;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .hero-section {
            padding: 4rem 0;
            color: white;
            text-align: center;
        }
        
        .hero-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .content-section {
            background: white;
            border-radius: 20px 20px 0 0;
            margin-top: -2rem;
            padding: 3rem 0;
        }
        
        .feature-card {
            padding: 2rem;
            border-radius: 15px;
            background: #f8f9fa;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 1.5rem;
            transition: all 0.3s;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .integration-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 50px;
            margin: 0.5rem;
            font-size: 0.9rem;
        }
        
        .role-card {
            text-align: center;
            padding: 2rem;
            border-radius: 15px;
            background: white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s;
        }
        
        .role-card:hover {
            transform: scale(1.05);
        }
        
        .role-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .stats-box {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px;
            margin-bottom: 1rem;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
            color: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .section-title p {
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .footer {
            background: #1f2937;
            color: white;
            padding: 2rem 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-icon">
                <i class="bi bi-award-fill"></i>
            </div>
            <h1 class="display-3 fw-bold mb-3">Sistema de Puntos</h1>
            <p class="lead mb-0">Plataforma integral de fidelización para comercios con integración automática</p>
        </div>
    </section>

    <!-- Content Section -->
    <div class="content-section">
        <!-- Características Principales -->
        <section id="funcionalidades" class="py-5">
            <div class="container">
                <div class="section-title">
                    <h2>Características Principales</h2>
                    <p>Todo lo que necesitás para gestionar tu programa de fidelización</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <h5 class="fw-bold">Acumulación Automática</h5>
                            <p class="text-muted mb-0">Integración con e-Factura. Cada compra genera puntos automáticamente sin intervención manual.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-gift"></i>
                            </div>
                            <h5 class="fw-bold">Sistema FIFO</h5>
                            <p class="text-muted mb-0">Canjes inteligentes con cupones PDF. Los puntos más antiguos se usan primero.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-whatsapp"></i>
                            </div>
                            <h5 class="fw-bold">WhatsApp Automático</h5>
                            <p class="text-muted mb-0">Notificaciones instantáneas: bienvenida, canjes, puntos por vencer y promociones.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-tags"></i>
                            </div>
                            <h5 class="fw-bold">Promociones Dinámicas</h5>
                            <p class="text-muted mb-0">Bonificaciones y multiplicadores (2x, 3x) con condiciones por fecha, monto o día de semana.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-megaphone"></i>
                            </div>
                            <h5 class="fw-bold">Campañas Masivas</h5>
                            <p class="text-muted mb-0">Envío por WhatsApp y Email con límites inteligentes. Programación y seguimiento en tiempo real.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-bar-chart"></i>
                            </div>
                            <h5 class="fw-bold">Reportes Completos</h5>
                            <p class="text-muted mb-0">Exportación CSV de clientes, facturas, canjes y actividades. Dashboard con métricas en tiempo real.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h5 class="fw-bold">Multi-Usuario</h5>
                            <p class="text-muted mb-0">3 roles con permisos granulares: Admin, Supervisor y Operario. Auditoría completa.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-globe"></i>
                            </div>
                            <h5 class="fw-bold">Portal Público</h5>
                            <p class="text-muted mb-0">Autoconsulta sin login. Clientes ven sus puntos, vencimientos y datos del comercio 24/7.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-sliders"></i>
                            </div>
                            <h5 class="fw-bold">Ajustes Manuales</h5>
                            <p class="text-muted mb-0">Suma o resta puntos con motivo obligatorio. Auditoría completa y protección contra saldos negativos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Integraciones -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="section-title">
                    <h2>Integraciones</h2>
                    <p>Conectá tu sistema de facturación y comunicaciones</p>
                </div>
                
                <div class="text-center">
                    <span class="integration-badge">
                        <i class="bi bi-receipt me-2"></i>e-Factura Uruguay
                    </span>
                    <span class="integration-badge">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp Business API
                    </span>
                    <span class="integration-badge">
                        <i class="bi bi-envelope me-2"></i>Email SMTP
                    </span>
                    <span class="integration-badge">
                        <i class="bi bi-currency-exchange me-2"></i>Multi-Moneda (UYU, USD, ARS)
                    </span>
                </div>
                
                <div class="row mt-5">
                    <div class="col-md-4 text-center">
                        <i class="bi bi-whatsapp" style="font-size: 3rem; color: var(--primary-color);"></i>
                        <h5 class="mt-3">Campañas WhatsApp</h5>
                        <p class="text-muted">Envío masivo con límite de 30 mensajes/minuto para proteger tu cuenta</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="bi bi-envelope-heart" style="font-size: 3rem; color: var(--primary-color);"></i>
                        <h5 class="mt-3">Campañas Email</h5>
                        <p class="text-muted">SMTP propio (50 emails/día) o servicio premium sin límites</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="bi bi-bullseye" style="font-size: 3rem; color: var(--primary-color);"></i>
                        <h5 class="mt-3">Segmentación Inteligente</h5>
                        <p class="text-muted">Envía a todos o grupos específicos con programación flexible</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Roles -->
        <section class="py-5">
            <div class="container">
                <div class="section-title">
                    <h2>Roles y Permisos</h2>
                    <p>Control granular de acceso según responsabilidades</p>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="role-card">
                            <div class="role-icon" style="color: #ef4444;">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <h4 class="fw-bold">Admin</h4>
                            <p class="text-muted">Control total del sistema</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="bi bi-check-circle text-success me-2"></i>Gestión de clientes</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Canjes y ajustes</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Promociones</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Campañas</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Usuarios</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Configuración</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="role-card">
                            <div class="role-icon" style="color: #f59e0b;">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <h4 class="fw-bold">Supervisor</h4>
                            <p class="text-muted">Operaciones y gestión</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="bi bi-check-circle text-success me-2"></i>Gestión de clientes</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Canjes y ajustes</li>
                                <li><i class="bi bi-eye text-primary me-2"></i>Ver promociones</li>
                                <li><i class="bi bi-eye text-primary me-2"></i>Ver campañas</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Reportes</li>
                                <li><i class="bi bi-x-circle text-danger me-2"></i>Sin config/usuarios</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="role-card">
                            <div class="role-icon" style="color: #10b981;">
                                <i class="bi bi-person"></i>
                            </div>
                            <h4 class="fw-bold">Operario</h4>
                            <p class="text-muted">Solo operaciones básicas</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="bi bi-check-circle text-success me-2"></i>Buscar clientes</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Ver detalles</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Canjear puntos</li>
                                <li><i class="bi bi-x-circle text-danger me-2"></i>Sin edición</li>
                                <li><i class="bi bi-x-circle text-danger me-2"></i>Sin reportes</li>
                                <li><i class="bi bi-x-circle text-danger me-2"></i>Sin config</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ventajas de Fidelizar -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="section-title">
                    <h2>¿Por Qué Fidelizar a Tus Clientes?</h2>
                    <p>Un programa de puntos bien implementado transforma tu negocio</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                            <h5 class="fw-bold">Aumenta las Visitas Recurrentes</h5>
                            <p class="text-muted mb-2">Los clientes con puntos acumulados vuelven más seguido para no perderlos.</p>
                            <p class="mb-0"><strong class="text-primary">+30% de visitas</strong> en promedio según estudios de retail</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-cart-plus"></i>
                            </div>
                            <h5 class="fw-bold">Incrementa el Ticket Promedio</h5>
                            <p class="text-muted mb-2">Los clientes compran más para alcanzar umbrales de puntos o promociones.</p>
                            <p class="mb-0"><strong class="text-primary">+20% en ticket</strong> con promociones por monto mínimo</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-heart"></i>
                            </div>
                            <h5 class="fw-bold">Genera Lealtad de Marca</h5>
                            <p class="text-muted mb-2">Un cliente fiel cuesta 5 veces menos que conseguir uno nuevo.</p>
                            <p class="mb-0"><strong class="text-primary">Retención a largo plazo</strong> vs. clientes ocasionales</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-megaphone"></i>
                            </div>
                            <h5 class="fw-bold">Comunicación Directa</h5>
                            <p class="text-muted mb-2">Canal directo por WhatsApp para promociones, novedades y ofertas exclusivas.</p>
                            <p class="mb-0"><strong class="text-primary">Mayor engagement</strong> que redes sociales</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <h5 class="fw-bold">Datos para Decisiones</h5>
                            <p class="text-muted mb-2">Conocé el comportamiento de compra, frecuencia y preferencias de tus clientes.</p>
                            <p class="mb-0"><strong class="text-primary">Reportes detallados</strong> para estrategias comerciales</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-trophy"></i>
                            </div>
                            <h5 class="fw-bold">Ventaja Competitiva</h5>
                            <p class="text-muted mb-2">Diferenciáte de la competencia con un programa profesional y automático.</p>
                            <p class="mb-0"><strong class="text-primary">Clientes prefieren</strong> comercios con beneficios</p>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-success mt-4 text-center">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Dato clave:</strong> El 80% de las ventas provienen del 20% de clientes fieles. 
                    Un programa de puntos te ayuda a identificar y retener ese 20%.
                </div>
            </div>
        </section>

        <!-- Estadísticas -->
        <section class="py-5">
            <div class="container">
                <div class="section-title">
                    <h2>Sistema Probado y Confiable</h2>
                    <p>Automatización completa para tu tranquilidad</p>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="stats-box">
                            <div class="stats-number">100%</div>
                            <div>Automático</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-box">
                            <div class="stats-number">4</div>
                            <div>Eventos de notificación</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-box">
                            <div class="stats-number">24/7</div>
                            <div>Portal público</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-box">
                            <div class="stats-number">3</div>
                            <div>Roles multi-usuario</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <h2 class="display-5 fw-bold mb-4">¿Listo para fidelizar a tus clientes?</h2>
                <p class="lead mb-4">Implementá tu programa de puntos en 1 día. Sin costos por transacción.</p>
                <p class="mb-0">
                    <i class="bi bi-envelope me-2"></i>
                    Contactanos para más información sobre implementación y precios
                </p>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center">
                        <p class="mb-2">
                            <i class="bi bi-award-fill me-2"></i>
                            <strong>Sistema de Puntos</strong>
                        </p>
                        <p class="mb-0 small text-muted">
                            © 2025 Sistema de Puntos - Todos los derechos reservados
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

