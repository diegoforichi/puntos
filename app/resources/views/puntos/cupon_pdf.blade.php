<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cupón</title>
    <style>
        @page { size: A4; margin: 5mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 8pt; line-height: 1.25; }
        table { width: 100%; border-collapse: collapse; }

        .page-table {
            table-layout: fixed;
            height: 287mm; /* 297mm - (5mm + 5mm) márgenes */
        }

        .cupon-row {
            height: 136mm; /* deja 15mm libres para redondeos */
            page-break-inside: avoid;
        }

        .cupon-cell {
            vertical-align: top;
            border: 1px dashed #000;
            padding: 3mm;
            word-break: break-word;
        }

        .titulo { text-align: center; font-size: 10pt; font-weight: bold; margin-bottom: 2mm; }
        .tag    { text-align: center; font-size: 7pt; font-style: italic; color: #666; margin-bottom: 2mm; }
        .codigo { text-align: center; font-size: 14pt; font-weight: bold; letter-spacing: 2px; margin: 2mm 0; }

        .seccion { font-size: 8pt; font-weight: bold; border-bottom: 1px solid #ccc; margin: 2mm 0 1mm; padding-bottom: 1mm; }
        .info-table td { padding: 1mm 0; font-size: 8pt; }

        .stats-table { width: 100%; margin: 2mm 0; }
        .stats-cell  { width: 50%; padding: 2mm; text-align: center; }
        .stats-box   { border: 1px solid #ddd; padding: 2mm; }
        .stats-label { font-size: 7pt; color: #666; }
        .stats-value { font-size: 11pt; font-weight: bold; }

        .nota   { background: #f5f5f5; border: 1px solid #ddd; padding: 2mm; font-size: 7pt; margin-top: 2mm; }
        .footer { font-size: 6pt; color: #666; border-top: 1px solid #eee; padding-top: 1mm; margin-top: 2mm; }
    </style>
</head>
<body>
@php
    $copias = [
        ['titulo' => 'COPIA CLIENTE',  'nota' => 'Presentar en caja.'],
        ['titulo' => 'COPIA COMERCIO', 'nota' => 'Archivo interno.'],
    ];
@endphp

<table class="page-table">
@foreach ($copias as $copia)
    <tr class="cupon-row">
        <td class="cupon-cell">
            <div class="titulo">{{ strtoupper($tenant->nombre_comercial) }} - CUPÓN DE CANJE</div>
            <div class="tag">{{ $copia['titulo'] }}</div>
            <div class="codigo">{{ $canje->codigo_cupon }}</div>

            <div class="seccion">CLIENTE</div>
            <table class="info-table">
                <tr>
                    <td width="50%"><strong>Nombre:</strong> {{ $canje->cliente->nombre }}</td>
                    <td width="50%"><strong>Doc:</strong> {{ $canje->cliente->documento }}</td>
                </tr>
            </table>

            <div class="seccion">DETALLE</div>
            <table class="stats-table">
                <tr>
                    <td class="stats-cell">
                        <div class="stats-box">
                            <div class="stats-label">Canjeados</div>
                            <div class="stats-value">{{ number_format($canje->puntos_canjeados, 2, ',', '.') }}</div>
                        </div>
                    </td>
                    <td class="stats-cell">
                        <div class="stats-box">
                            <div class="stats-label">Restantes</div>
                            <div class="stats-value">{{ number_format($canje->puntos_restantes, 2, ',', '.') }}</div>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="info-table">
                <tr>
                    <td width="50%"><strong>Concepto:</strong> {{ $canje->concepto ?? 'Canje' }}</td>
                    <td width="50%"><strong>Fecha:</strong> {{ $canje->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Autorizado:</strong> {{ $canje->autorizadoPor->nombre }}</td>
                </tr>
            </table>

            <div class="nota"><strong>{{ $copia['nota'] }}</strong> No transferible ni canjeable por efectivo.</div>
            <div class="footer">{{ $generadoEn->format('d/m/Y H:i') }} | ID: {{ $canje->id }} | Sistema v1.3</div>
        </td>
    </tr>
@endforeach
</table>
</body>
</html>