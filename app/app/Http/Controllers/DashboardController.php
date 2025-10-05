<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\PuntosCanjeado;
use App\Models\Actividad;

/**
 * Controlador del Dashboard
 * 
 * Muestra estadísticas principales del tenant:
 * - Total de clientes
 * - Total de puntos acumulados
 * - Facturas procesadas este mes
 * - Puntos canjeados
 */
class DashboardController extends Controller
{
    /**
     * Mostrar dashboard principal
     * 
     * GET /{tenant}/dashboard
     */
    public function index(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');

        // Obtener estadísticas principales
        $stats = $this->getStats();
        
        // Obtener clientes recientes
        $clientesRecientes = $this->getClientesRecientes();
        
        // Obtener actividad reciente
        $actividadReciente = $this->getActividadReciente();

        return view('dashboard.index', [
            'tenant' => $tenant,
            'usuario' => $usuario,
            'stats' => $stats,
            'clientesRecientes' => $clientesRecientes,
            'actividadReciente' => $actividadReciente,
        ]);
    }

    /**
     * Obtener estadísticas principales
     * 
     * @return array
     */
    private function getStats()
    {
        // Total de clientes
        $totalClientes = Cliente::count();
        
        // Total de puntos acumulados
        $totalPuntos = Cliente::sum('puntos_acumulados');
        
        // Facturas del mes actual
        $facturasMes = Factura::delMes()->count();
        
        // Puntos generados este mes
        $puntosGeneradosMes = Factura::delMes()->sum('puntos_generados');
        
        // Puntos canjeados este mes
        $puntosCanjeadosMes = PuntosCanjeado::delMes()->sum('puntos_canjeados');
        
        // Clientes activos (con actividad en los últimos 30 días)
        $clientesActivos = Cliente::activos(30)->count();
        
        // Facturas pendientes por vencer (próximos 30 días)
        $facturasPorVencer = Factura::porVencer(30)->count();

        return [
            'totalClientes' => $totalClientes,
            'totalPuntos' => number_format($totalPuntos, 2, ',', '.'),
            'facturasMes' => $facturasMes,
            'puntosGeneradosMes' => number_format($puntosGeneradosMes, 2, ',', '.'),
            'puntosCanjeadosMes' => number_format($puntosCanjeadosMes, 2, ',', '.'),
            'clientesActivos' => $clientesActivos,
            'facturasPorVencer' => $facturasPorVencer,
        ];
    }

    /**
     * Obtener clientes recientes
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getClientesRecientes()
    {
        return Cliente::select('id', 'documento', 'nombre', 'puntos_acumulados', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Obtener actividad reciente
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getActividadReciente()
    {
        return Actividad::with('usuario:id,nombre')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
}
