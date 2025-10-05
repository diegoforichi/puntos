<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Webhook para recibir facturas de eFactura
Route::post('/webhook/ingest', [WebhookController::class, 'ingest'])->name('api.webhook.ingest');

// 🔍 PING: Verificar que el endpoint está online (sin autenticación)
Route::get('/webhook/ping', function() {
    return response()->json([
        'status' => 'online',
        'timestamp' => now()->toDateTimeString(),
        'message' => 'Webhook endpoint is alive',
    ]);
})->name('api.webhook.ping');


if (env('WEBHOOK_DEBUG_ENABLED', false)) {
    Route::post('/webhook/debug', function(Request $request) {
        $token = $request->header('X-Debug-Token');
        $expected = env('WEBHOOK_DEBUG_TOKEN');

        if (!$token || !$expected || !hash_equals($expected, $token)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized debug access'], 403);
        }

        \Log::info('🔍 WEBHOOK DEBUG RECEIVED', [
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw' => $request->getContent(),
        ]);
        
        return response()->json([
            'status' => 'received',
            'timestamp' => now()->toDateTimeString(),
            'logged' => true,
        ]);
    })->name('api.webhook.debug');
}
