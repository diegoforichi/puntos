<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TenantTestWebhookController extends Controller
{
    public function __invoke(Request $request, Tenant $tenant)
    {
        $payloadPath = base_path('scripts/hookCfe.json');

        if (!file_exists($payloadPath)) {
            return back()->with('error', 'No se encontró el archivo scripts/hookCfe.json.');
        }

        $payload = json_decode(file_get_contents($payloadPath), true);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $tenant->api_key,
            'Content-Type' => 'application/json',
        ])->post(route('api.webhook.ingest'), $payload);

        if ($response->successful()) {
            return back()->with('success', 'Webhook de prueba enviado correctamente.');
        }

        return back()->with('error', 'Error al enviar webhook: ' . $response->body());
    }
}
