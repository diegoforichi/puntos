<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\PuntosCanjeado;
use Illuminate\Http\Request;

/**
 * Controlador de Reportes
 *
 * Genera reportes del sistema:
 * - Clientes con puntos
 * - Facturas procesadas
 * - Canjes realizados
 * - Actividad del sistema
 *
 * Exporta a CSV
 */
class ReporteController extends Controller
{
    /**
     * Mostrar página de reportes
     *
     * GET /{tenant}/reportes
     */
    public function index(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $usuario = $request->attributes->get('usuario');

        return view('reportes.index', [
            'tenant' => $tenant,
            'usuario' => $usuario,
        ]);
    }

    /**
     * Generar reporte de clientes
     *
     * GET /{tenant}/reportes/clientes
     */
    public function clientes(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $formato = $request->get('formato', 'html');

        // Filtros
        $estado = $request->get('estado');
        $orden = $request->get('orden', 'puntos_desc');

        // Query
        $query = Cliente::query();

        if ($estado === 'con_puntos') {
            $query->conPuntos();
        } elseif ($estado === 'sin_puntos') {
            $query->where('puntos_acumulados', '<=', 0);
        }

        // Ordenar
        [$campo, $direccion] = explode('_', $orden);
        if ($campo === 'puntos') {
            $query->orderBy('puntos_acumulados', $direccion);
        } elseif ($campo === 'nombre') {
            $query->orderBy('nombre', $direccion);
        } else {
            $query->orderBy('created_at', $direccion ?? 'desc');
        }

        $totalClientes = (clone $query)->count();
        $totalPuntos = (clone $query)->sum('puntos_acumulados');
        $promedioPuntos = $totalClientes > 0 ? $totalPuntos / $totalClientes : 0;

        if ($formato === 'csv') {
            $clientes = $query->get();
        } else {
            $clientes = $query->paginate(50)->onEachSide(1)->withQueryString();
        }

        // Exportar CSV
        if ($formato === 'csv') {
            return $this->exportarClientesCSV($clientes);
        }

        // Vista HTML
        return view('reportes.clientes', [
            'tenant' => $tenant,
            'clientes' => $clientes,
            'filtros' => [
                'estado' => $estado,
                'orden' => $orden,
            ],
            'estadisticas' => [
                'total' => $totalClientes,
                'suma_puntos' => $totalPuntos,
                'promedio' => $promedioPuntos,
            ],
        ]);
    }

    /**
     * Generar reporte de facturas
     *
     * GET /{tenant}/reportes/facturas
     */
    public function facturas(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $formato = $request->get('formato', 'html');

        // Filtros
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $estado = $request->get('estado');

        // Query
        $query = Factura::with('cliente:id,nombre,documento');

        if ($fechaInicio) {
            $query->where('fecha_emision', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->where('fecha_emision', '<=', $fechaFin);
        }

        if ($estado === 'activas') {
            $query->activas();
        } elseif ($estado === 'vencidas') {
            $query->vencidas();
        }

        if ($formato === 'csv') {
            $facturas = $query->orderBy('fecha_emision', 'desc')->get();
        } else {
            $facturas = $query->orderBy('fecha_emision', 'desc')
                ->paginate(50)->onEachSide(1)->withQueryString();
        }

        // Exportar CSV
        if ($formato === 'csv') {
            return $this->exportarFacturasCSV($facturas);
        }

        // Vista HTML
        return view('reportes.facturas', [
            'tenant' => $tenant,
            'facturas' => $facturas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => $estado,
            ],
        ]);
    }

    /**
     * Generar reporte de canjes
     *
     * GET /{tenant}/reportes/canjes
     */
    public function canjes(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $formato = $request->get('formato', 'html');

        // Filtros
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        // Query
        $query = PuntosCanjeado::with(['cliente:id,nombre,documento', 'autorizadoPor:id,nombre']);

        if ($fechaInicio) {
            $query->whereDate('created_at', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('created_at', '<=', $fechaFin);
        }

        if ($formato === 'csv') {
            $canjes = $query->orderBy('created_at', 'desc')->get();
        } else {
            $canjes = $query->orderBy('created_at', 'desc')
                ->paginate(50)->onEachSide(1)->withQueryString();
        }

        // Exportar CSV
        if ($formato === 'csv') {
            return $this->exportarCanjesCSV($canjes);
        }

        // Vista HTML
        return view('reportes.canjes', [
            'tenant' => $tenant,
            'canjes' => $canjes,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }

    /**
     * Generar reporte de actividades
     *
     * GET /{tenant}/reportes/actividades
     */
    public function actividades(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $formato = $request->get('formato', 'html');

        // Filtros
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $accion = $request->get('accion');

        // Query
        $query = Actividad::with('usuario:id,nombre');

        if ($fechaInicio) {
            $query->whereDate('created_at', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('created_at', '<=', $fechaFin);
        }

        if ($accion) {
            $query->where('accion', $accion);
        }

        $actividades = $query->orderBy('created_at', 'desc')->limit(500)->get();

        // Exportar CSV
        if ($formato === 'csv') {
            return $this->exportarActividadesCSV($actividades);
        }

        // Vista HTML
        return view('reportes.actividades', [
            'tenant' => $tenant,
            'actividades' => $actividades,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'accion' => $accion,
            ],
        ]);
    }

    /**
     * Exportar clientes a CSV
     */
    private function exportarClientesCSV($clientes)
    {
        $filename = 'clientes_'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($clientes) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($file, ['Documento', 'Nombre', 'Teléfono', 'Email', 'Puntos Acumulados', 'Registrado']);

            // Datos
            foreach ($clientes as $cliente) {
                fputcsv($file, [
                    $cliente->documento,
                    $cliente->nombre,
                    $cliente->telefono ?? '',
                    $cliente->email ?? '',
                    number_format($cliente->puntos_acumulados, 2, ',', '.'),
                    $cliente->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar facturas a CSV
     */
    private function exportarFacturasCSV($facturas)
    {
        $filename = 'facturas_'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($facturas) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['N° Factura', 'Cliente', 'Documento', 'Monto', 'Puntos', 'Fecha Emisión', 'Vencimiento', 'Estado']);

            foreach ($facturas as $factura) {
                fputcsv($file, [
                    $factura->numero_factura,
                    $factura->cliente->nombre ?? '',
                    $factura->cliente->documento ?? '',
                    number_format($factura->monto_total, 2, ',', '.'),
                    number_format($factura->puntos_generados, 2, ',', '.'),
                    $factura->fecha_emision->format('d/m/Y'),
                    $factura->fecha_vencimiento->format('d/m/Y'),
                    $factura->estaVencida() ? 'Vencida' : 'Activa',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar canjes a CSV
     */
    private function exportarCanjesCSV($canjes)
    {
        $filename = 'canjes_'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($canjes) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Código', 'Cliente', 'Documento', 'Puntos Canjeados', 'Concepto', 'Autorizado Por', 'Fecha']);

            foreach ($canjes as $canje) {
                $puntosFirmados = $canje->puntos_firmados ?? (-1 * $canje->puntos_canjeados);
                fputcsv($file, [
                    $canje->codigo_cupon,
                    $canje->cliente->nombre ?? '',
                    $canje->cliente->documento ?? '',
                    ($puntosFirmados >= 0 ? '+' : '-').number_format(abs($puntosFirmados), 2, ',', '.'),
                    $canje->concepto,
                    $canje->autorizadoPor->nombre ?? 'Sistema',
                    $canje->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar actividades a CSV
     */
    private function exportarActividadesCSV($actividades)
    {
        $filename = 'actividades_'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($actividades) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Fecha', 'Usuario', 'Acción', 'Descripción']);

            foreach ($actividades as $actividad) {
                fputcsv($file, [
                    $actividad->created_at->format('d/m/Y H:i:s'),
                    $actividad->usuario->nombre ?? 'Sistema',
                    $actividad->accion,
                    $actividad->descripcion,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
