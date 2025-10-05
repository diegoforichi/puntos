<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Puntos')@isset($tenant) - {{ $tenant->nombre_comercial }}@endisset</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8fafc;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }
        
        /* Responsive sidebar */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
        }
        
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.25rem;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: var(--primary-color);
        }
        
        .sidebar .nav-link.active {
            background-color: rgba(37, 99, 235, 0.2);
            color: white;
            border-left-color: var(--primary-color);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .navbar-custom {
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 2px solid #f1f5f9;
            font-weight: 600;
        }
        
        .stat-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-card.success {
            border-left-color: var(--success-color);
        }
        
        .stat-card.warning {
            border-left-color: var(--warning-color);
        }
        
        .stat-card.danger {
            border-left-color: var(--danger-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        
        .alert {
            border: none;
            border-radius: 0.5rem;
        }
        
        .badge-role-admin {
            background-color: #7c3aed;
        }
        
        .badge-role-supervisor {
            background-color: #2563eb;
        }
        
        .badge-role-operario {
            background-color: #64748b;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @if(isset($usuario) && isset($tenant))
    <!-- Mobile Sidebar Toggle -->
<button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list" style="font-size: 1.5rem;"></i>
    </button>
    
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="p-4 border-bottom border-secondary">
            <h5 class="mb-1">{{ $tenant->nombre_comercial }}</h5>
            <small class="text-muted">RUT: {{ $tenant->rut }}</small>
        </div>
        
        <nav class="nav flex-column mt-3">
            <a class="nav-link {{ request()->is('*/dashboard') ? 'active' : '' }}" href="/{{ $tenant->rut }}/dashboard">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <a class="nav-link {{ request()->is('*/clientes*') ? 'active' : '' }}" href="/{{ $tenant->rut }}/clientes">
                <i class="bi bi-people"></i> Clientes
            </a>
            
            @if(in_array($usuario->rol, ['admin', 'supervisor']))
            <a class="nav-link {{ request()->is('*/puntos*') ? 'active' : '' }}" href="/{{ $tenant->rut }}/puntos/canjear">
                <i class="bi bi-gift"></i> Canjear Puntos
            </a>
            @endif
            
            @if($usuario->rol === 'admin')
            <a class="nav-link {{ request()->is('*/promociones*') ? 'active' : '' }}" href="/{{ $tenant->rut }}/promociones">
                <i class="bi bi-tags"></i> Promociones
            </a>
            
            <a class="nav-link {{ request()->is('*/usuarios*') ? 'active' : '' }}" href="/{{ $tenant->rut }}/usuarios">
                <i class="bi bi-person-badge"></i> Usuarios
            </a>
            
            <a class="nav-link {{ request()->is('*/configuracion*') ? 'active' : '' }}" href="/{{ $tenant->rut }}/configuracion">
                <i class="bi bi-gear"></i> Configuración
            </a>
            @endif
            
            <a class="nav-link {{ request()->is('*/reportes*') ? 'active' : '' }}" href="/{{ $tenant->rut }}/reportes">
                <i class="bi bi-file-earmark-bar-graph"></i> Reportes
            </a>
            
            <hr class="text-secondary">
            
            <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
            </a>
        </nav>
        
        <form id="logout-form" action="/{{ $tenant->rut }}/logout" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">@yield('page-title', 'Dashboard')</span>
                
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3">
                        <i class="bi bi-person-circle"></i> {{ $usuario->nombre }}
                        <span class="badge badge-role-{{ $usuario->rol }} ms-2">
                            {{ ucfirst($usuario->rol) }}
                        </span>
                    </span>
                </div>
            </div>
        </nav>
    @endif
    
    <!-- Alerts -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show alert-auto" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show alert-auto" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show alert-auto" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show alert-auto" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    
    <!-- Page Content -->
    @yield('content')
    
    @if(isset($usuario) && isset($tenant))
    </div><!-- Close main-content -->
    @endif
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Auto-hide alerts after 5 seconds únicamente para las alertas marcadas como auto
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert.alert-auto');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Sidebar toggle for mobile
        @if(isset($usuario) && isset($tenant))
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        if (sidebarToggle && sidebar && sidebarOverlay) {
            // Toggle sidebar
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            });
            
            // Close sidebar when clicking overlay
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            });
            
            // Close sidebar when clicking on a link (mobile)
            const sidebarLinks = sidebar.querySelectorAll('.nav-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                    }
                });
            });
        }
        @endif
    </script>
    
    @stack('scripts')
</body>
</html>
