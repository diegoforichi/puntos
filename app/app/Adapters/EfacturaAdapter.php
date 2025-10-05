<?php

namespace App\Adapters;

use App\Contracts\InvoiceAdapter;
use App\DTOs\StandardInvoiceDTO;

/**
 * Adaptador para sistema de eFactura
 * Convierte el JSON de eFactura al formato estándar
 */
class EfacturaAdapter implements InvoiceAdapter
{
    /**
     * Verificar si el payload es de eFactura
     */
    public function matches(array $payload): bool
    {
        // Verificar estructura mínima esencial del payload
        // Acepta tanto el JSON completo de hookCfe.json como versiones simplificadas
        return isset($payload['CfeId'])
            && isset($payload['Client']['NroDoc'])
            && (
                isset($payload['Totales']['TotMntTotal'])
                || isset($payload['Total']['TotMntTotal'])
            );
    }
    
    /**
     * Convertir a DTO estándar
     */
    public function toStandard(array $payload): StandardInvoiceDTO
    {
        $totales = $payload['Totales'] ?? $payload['Total'] ?? [];

        return new StandardInvoiceDTO(
            rutEmisor: $payload['Emisor']['RUT'] ?? '',
            numeroFactura: $payload['Numero'] ?? ($payload['IdDoc']['Folio'] ?? ''),
            documentoCliente: $payload['Client']['NroDoc'] ?? ($payload['Client']['Documento'] ?? ''),
            nombreCliente: $payload['Client']['Nombre'] ?? ($payload['Client']['RznSoc'] ?? 'Cliente'),
            telefonoCliente: $payload['Client']['Telefono'] ?? ($payload['Client']['NroTel'] ?? null),
            emailCliente: $payload['Client']['Email'] ?? null,
            montoTotal: (float) ($totales['TotMntTotal'] ?? 0),
            moneda: $totales['TpoMoneda']
                ?? ($payload['Money']['Valor'] ?? 'UYU'),
            fechaEmision: $this->formatearFecha($payload['FechaEmision'] ?? ($payload['FecEmis'] ?? null)),
            detalle: $payload['Detalle'] ?? null,
            payloadOriginal: $payload,
            cfeId: isset($payload['CfeId']) ? (int) $payload['CfeId'] : ($payload['IdDoc']['TipoCFE'] ?? null),
            tipoDocumentoCliente: $payload['Client']['TipoDocumento'] ?? null,
            codigoTipoDocumentoCliente: $payload['Client']['CodigoTipoDocumento'] ?? null
        );
    }
    
    /**
     * Obtener nombre del adaptador
     */
    public function getName(): string
    {
        return 'efactura';
    }
    
    /**
     * Limpiar número de teléfono (soporta formatos UY y CL)
     */
    private function limpiarTelefono(?string $telefono): ?string
    {
        if (!$telefono) {
            return null;
        }

        return trim($telefono);
    }
    
    /**
     * Formatear fecha a ISO 8601
     */
    private function formatearFecha(?string $fecha): string
    {
        if (!$fecha) {
            return now()->toIso8601String();
        }
        
        try {
            return \Carbon\Carbon::parse($fecha)->toIso8601String();
        } catch (\Exception $e) {
            return now()->toIso8601String();
        }
    }
}
