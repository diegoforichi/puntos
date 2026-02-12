<?php

namespace Tests\Feature;

use App\Jobs\EnviarNotificacionWhatsApp;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class EnviarNotificacionWhatsAppTest extends TestCase
{
    /**
     * Test que el job se puede instanciar correctamente.
     */
    public function test_job_se_instancia_con_parametros_correctos(): void
    {
        $job = new EnviarNotificacionWhatsApp(
            tenantId: 1,
            tipo: EnviarNotificacionWhatsApp::TIPO_CANJE,
            cliente: ['nombre' => 'Test', 'telefono' => '099123456'],
            datosExtra: ['puntos_canjeados' => 50, 'puntos_restantes' => 100]
        );

        $this->assertEquals(1, $job->tenantId);
        $this->assertEquals(EnviarNotificacionWhatsApp::TIPO_CANJE, $job->tipo);
        $this->assertEquals('campanas', $job->queue);
    }

    /**
     * Test que el job puede ser despachado a la cola.
     */
    public function test_job_puede_ser_despachado(): void
    {
        Bus::fake();

        EnviarNotificacionWhatsApp::dispatch(
            tenantId: 1,
            tipo: EnviarNotificacionWhatsApp::TIPO_PROMOCION,
            cliente: ['nombre' => 'Cliente Test', 'telefono' => '099999999'],
            datosExtra: ['descripcion' => 'Promo test', 'fecha_fin' => '2025-12-31']
        );

        Bus::assertDispatched(EnviarNotificacionWhatsApp::class, function ($job) {
            return $job->tipo === EnviarNotificacionWhatsApp::TIPO_PROMOCION
                && $job->cliente['telefono'] === '099999999';
        });
    }

    /**
     * Test que el job puede ser despachado con delay.
     */
    public function test_job_puede_ser_despachado_con_delay(): void
    {
        Bus::fake();

        $delaySeconds = 10;

        EnviarNotificacionWhatsApp::dispatch(
            tenantId: 1,
            tipo: EnviarNotificacionWhatsApp::TIPO_CANJE,
            cliente: ['nombre' => 'Test Delay', 'telefono' => '099111222'],
            datosExtra: []
        )->delay(now()->addSeconds($delaySeconds));

        Bus::assertDispatched(EnviarNotificacionWhatsApp::class);
    }

    /**
     * Test que se usan las constantes de tipo correctamente.
     */
    public function test_constantes_de_tipo_definidas(): void
    {
        $this->assertEquals('canje', EnviarNotificacionWhatsApp::TIPO_CANJE);
        $this->assertEquals('promocion', EnviarNotificacionWhatsApp::TIPO_PROMOCION);
    }
}
