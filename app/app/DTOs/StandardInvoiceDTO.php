<?php

namespace App\DTOs;

/**
 * DTO estándar para facturas
 * Todos los adaptadores convierten su formato a este
 */
class StandardInvoiceDTO
{
    public function __construct(
        public string $rutEmisor,
        public string $numeroFactura,
        public string $documentoCliente,
        public string $nombreCliente,
        public ?string $telefonoCliente,
        public ?string $emailCliente,
        public float $montoTotal,
        public string $moneda,
        public string $fechaEmision,
        public ?array $detalle = null,
        public ?array $payloadOriginal = null,
        public ?int $cfeId = null,
        public ?string $tipoDocumentoCliente = null,
        public ?string $codigoTipoDocumentoCliente = null
    ) {}
    
    /**
     * Crear desde array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            rutEmisor: $data['rut_emisor'] ?? '',
            numeroFactura: $data['numero_factura'] ?? '',
            documentoCliente: $data['documento_cliente'] ?? '',
            nombreCliente: $data['nombre_cliente'] ?? '',
            telefonoCliente: $data['telefono_cliente'] ?? null,
            emailCliente: $data['email_cliente'] ?? null,
            montoTotal: (float) ($data['monto_total'] ?? 0),
            moneda: $data['moneda'] ?? 'UYU',
            fechaEmision: $data['fecha_emision'] ?? now()->toIso8601String(),
            detalle: $data['detalle'] ?? null,
            payloadOriginal: $data['payload_original'] ?? null,
            cfeId: isset($data['cfe_id']) ? (int) $data['cfe_id'] : null,
            tipoDocumentoCliente: $data['tipo_documento_cliente'] ?? null,
            codigoTipoDocumentoCliente: $data['codigo_tipo_documento_cliente'] ?? null
        );
    }
    
    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'rut_emisor' => $this->rutEmisor,
            'numero_factura' => $this->numeroFactura,
            'documento_cliente' => $this->documentoCliente,
            'nombre_cliente' => $this->nombreCliente,
            'telefono_cliente' => $this->telefonoCliente,
            'email_cliente' => $this->emailCliente,
            'monto_total' => $this->montoTotal,
            'moneda' => $this->moneda,
            'fecha_emision' => $this->fechaEmision,
            'detalle' => $this->detalle,
            'payload_original' => $this->payloadOriginal,
            'cfe_id' => $this->cfeId,
            'tipo_documento_cliente' => $this->tipoDocumentoCliente,
            'codigo_tipo_documento_cliente' => $this->codigoTipoDocumentoCliente,
        ];
    }
}
