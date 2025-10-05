<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel SuperAdmin')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fb;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #111827 0%, #1f2937 100%);
            color: #fff;
        }
        .sidebar a {
            color: #cbd5f5;
            text-decoration: none;
            display: block;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
        }
        .sidebar a.active,
        .sidebar a:hover {
            background-color: rgba(59, 130, 246, 0.25);
            color: #fff;
        }
        .content-wrapper {
            min-height: 100vh;
        }
    </style>
</head>
<body>
@if(isset($superadmin))
    <div class="container-fluid">
        <div class="row">
            <aside class="col-12 col-md-3 col-lg-2 sidebar py-4">
                <div class="px-3 mb-4">
                    <h5 class="mb-0">Panel SuperAdmin</h5>
                    <small>Control Global</small>
                </div>
                <nav class="px-2 d-flex flex-column gap-2">
                    <a href="{{ route('superadmin.dashboard') }}" class="{{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('superadmin.config') }}" class="{{ request()->routeIs('superadmin.config') ? 'active' : '' }}">
                        <i class="bi bi-gear me-2"></i> Configuración Global
                    </a>
                    <a href="{{ route('superadmin.tenants.index') }}" class="{{ request()->routeIs('superadmin.tenants.index') ? 'active' : '' }}">
                        <i class="bi bi-building me-2"></i> Tenants
                    </a>
                    <a href="{{ route('superadmin.webhooks') }}" class="{{ request()->routeIs('superadmin.webhooks') ? 'active' : '' }}">
                        <i class="bi bi-inbox-arrow-down me-2"></i> Webhooks
                    </a>
                    <form action="{{ route('superadmin.logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-light w-100">
                            <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                        </button>
                    </form>
                </nav>
            </aside>
            <main class="col-12 col-md-9 col-lg-10 content-wrapper py-4">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-0">@yield('page-title', 'Panel SuperAdmin')</h2>
                            <small class="text-muted">@yield('page-subtitle')</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold">{{ $superadmin->name }}</div>
                            <small class="text-muted">{{ $superadmin->email }}</small>
                        </div>
                    </div>

                    @foreach (['success','error','warning','info'] as $type)
                        @if(session($type))
                            <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show" role="alert">
                                {!! session($type) !!}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        @endif
                    @endforeach

                    @yield('content')
                    @stack('modals')
                </div>
            </main>
        </div>
    </div>
@else
    <div class="container py-5">
        @yield('content')
    </div>
@endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
