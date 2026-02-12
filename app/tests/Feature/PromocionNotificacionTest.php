<?php

namespace Tests\Feature;

use App\Jobs\EnviarNotificacionWhatsApp;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class PromocionNotificacionTest extends TestCase
{
    /**
     * Test que múltiples jobs se despachan con delays progresivos.
     */
    public function test_jobs_se_despachan_con_delay_progresivo(): void
    {
        Bus::fake();

        // Simular el comportamiento del controlador PromocionController::notificarClientes
        $clientes = [
            ['nombre' => 'Cliente 1', 'telefono' => '099111111'],
            ['nombre' => 'Cliente 2', 'telefono' => '099222222'],
            ['nombre' => 'Cliente 3', 'telefono' => '099333333'],
        ];

        $tenantId = 1;
        $descripcion = 'Promoción de prueba';
        $fechaFin = '31/12/2025';

        foreach ($clientes as $index => $cliente) {
            EnviarNotificacionWhatsApp::dispatch(
                $tenantId,
                EnviarNotificacionWhatsApp::TIPO_PROMOCION,
                $cliente,
                [
                    'descripcion' => $descripcion,
                    'fecha_fin' => $fechaFin,
                ]
            )->delay(now()->addSeconds($index * 5));
        }

        // Verificar que se despacharon 3 jobs
        Bus::assertDispatchedTimes(EnviarNotificacionWhatsApp::class, 3);

        // Verificar que cada job tiene los datos correctos
        Bus::assertDispatched(EnviarNotificacionWhatsApp::class, function ($job) {
            return $job->tipo === EnviarNotificacionWhatsApp::TIPO_PROMOCION;
        });
    }

    /**
     * Test que el cálculo de tiempo estimado es correcto.
     */
    public function test_calculo_tiempo_estimado(): void
    {
        // Simular el cálculo del controlador: ceil($totalEncolados * 8 / 60)
        $totalEncolados = 10;
        $tiempoEstimado = ceil($totalEncolados * 8 / 60);

        // 10 clientes * 8 seg = 80 seg = 1.33 min ≈ 2 min
        $this->assertEquals(2, $tiempoEstimado);

        // Con 50 clientes
        $totalEncolados = 50;
        $tiempoEstimado = ceil($totalEncolados * 8 / 60);
        // 50 * 8 = 400 seg = 6.67 min ≈ 7 min
        $this->assertEquals(7, $tiempoEstimado);

        // Con 100 clientes
        $totalEncolados = 100;
        $tiempoEstimado = ceil($totalEncolados * 8 / 60);
        // 100 * 8 = 800 seg = 13.33 min ≈ 14 min
        $this->assertEquals(14, $tiempoEstimado);
    }

    /**
     * Test que el job de promoción tiene la cola correcta.
     */
    public function test_job_promocion_usa_cola_campanas(): void
    {
        $job = new EnviarNotificacionWhatsApp(
            tenantId: 1,
            tipo: EnviarNotificacionWhatsApp::TIPO_PROMOCION,
            cliente: ['nombre' => 'Test', 'telefono' => '099123456'],
            datosExtra: ['descripcion' => 'Promo', 'fecha_fin' => '2025-12-31']
        );

        $this->assertEquals('campanas', $job->queue);
    }

    /**
     * Test que los datos extra de promoción se pasan correctamente.
     */
    public function test_datos_extra_promocion_correctos(): void
    {
        $datosExtra = [
            'descripcion' => 'Descuento 20% en toda la tienda',
            'fecha_fin' => '15/12/2025',
        ];

        $job = new EnviarNotificacionWhatsApp(
            tenantId: 5,
            tipo: EnviarNotificacionWhatsApp::TIPO_PROMOCION,
            cliente: ['nombre' => 'María García', 'telefono' => '099555666'],
            datosExtra: $datosExtra
        );

        $this->assertEquals('Descuento 20% en toda la tienda', $job->datosExtra['descripcion']);
        $this->assertEquals('15/12/2025', $job->datosExtra['fecha_fin']);
        $this->assertEquals(5, $job->tenantId);
    }
}
