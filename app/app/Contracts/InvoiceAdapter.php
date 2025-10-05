<?php

namespace App\Contracts;

use App\DTOs\StandardInvoiceDTO;

/**
 * Interface para adaptadores de diferentes sistemas de facturación
 */
interface InvoiceAdapter
{
    /**
     * Verificar si el payload coincide con este adaptador
     */
    public function matches(array $payload): bool;
    
    /**
     * Convertir el payload a DTO estándar
     */
    public function toStandard(array $payload): StandardInvoiceDTO;
    
    /**
     * Obtener nombre del adaptador
     */
    public function getName(): string;
}
