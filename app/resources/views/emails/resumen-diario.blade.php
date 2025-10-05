<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .section { margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .section:last-child { border-bottom: none; }
        .section h2 { color: #667eea; font-size: 18px; margin: 0 0 15px 0; }
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .stat-item { background: #f8f9fa; padding: 15px; border-radius: 6px; }
        .stat-item label { display: block; font-size: 12px; color: #6c757d; margin-bottom: 5px; }
        .stat-item value { display: block; font-size: 24px; font-weight: bold; color: #212529; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; }
        .footer a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Resumen Diario</h1>
            <p style="margin: 10px 0 0 0;">{{ $tenant->nombre_comercial }}</p>
            <p style="margin: 5px 0 0 0; font-size: 14px;">{{ now()->subDay()->format('d/m/Y') }}</p>
        </div>

        <div class="content">
            <div class="section">
                <h2>📈 Actividad del Día</h2>
                <div class="stat-grid">
                    <div class="stat-item">
                        <label>Facturas procesadas</label>
                        <value>{{ number_format($stats['facturas_hoy']) }}</value>
                    </div>
                    <div class="stat-item">
                        <label>Puntos generados</label>
                        <value>{{ number_format($stats['puntos_generados_hoy'], 0) }}</value>
                    </div>
                    <div class="stat-item">
                        <label>Puntos canjeados</label>
                        <value>{{ number_format($stats['puntos_canjeados_hoy'], 0) }}</value>
                    </div>
                    <div class="stat-item">
                        <label>Nuevos clientes</label>
                        <value>{{ number_format($stats['nuevos_clientes_hoy']) }}</value>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>💰 Estado General</h2>
                <div class="stat-grid">
                    <div class="stat-item">
                        <label>Total clientes activos</label>
                        <value>{{ number_format($stats['total_clientes']) }}</value>
                    </div>
                    <div class="stat-item">
                        <label>Puntos en circulación</label>
                        <value>{{ number_format($stats['puntos_circulacion'], 0) }}</value>
                    </div>
                    <div class="stat-item">
                        <label>Facturas del mes</label>
                        <value>{{ number_format($stats['facturas_mes']) }}</value>
                    </div>
                    <div class="stat-item">
                        <label>⚠️ Puntos por vencer (7 días)</label>
                        <value>{{ number_format($stats['clientes_por_vencer']) }}</value>
                    </div>
                </div>
            </div>

            <div class="section" style="border-bottom: none;">
                <p style="margin: 0; text-align: center;">
                    <a href="{{ config('app.url') }}/{{ $tenant->rut }}/login" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold;">
                        Acceder al Panel
                    </a>
                </p>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0;">Sistema de Puntos</p>
            <p style="margin: 5px 0 0 0;">Este es un resumen automático generado diariamente.</p>
        </div>
    </div>
</body>
</html>

