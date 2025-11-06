<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $contenido['titulo'] ?? $campana->titulo }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="background-color: #2563eb; padding: 24px; color: #ffffff; text-align: center;">
                <h1 style="margin: 0; font-size: 24px;">{{ $contenido['titulo'] ?? $campana->titulo }}</h1>
                @if(!empty($contenido['subtitulo']))
                    <p style="margin: 8px 0 0; font-size: 16px;">{{ $contenido['subtitulo'] }}</p>
                @endif
            </td>
        </tr>

        @if(!empty($contenido['imagen_url']))
        <tr>
            <td style="padding: 0;">
                <img src="{{ $contenido['imagen_url'] }}" alt="Campaña" style="width: 100%; display: block;">
            </td>
        </tr>
        @endif

        <tr>
            <td style="padding: 24px; color: #1f2937; line-height: 1.6;">
                {!! nl2br(e(str_replace(['{nombre}', '{puntos}'], [$cliente->nombre, number_format($cliente->puntos_acumulados, 2, ',', '.')], $contenido['cuerpo'] ?? ''))) !!}
            </td>
        </tr>

        <tr>
            <td style="padding: 16px 24px; background-color: #f9fafb; color: #6b7280; font-size: 12px; text-align: center;">
                @php
                    $nombreComercial = $contacto['nombre_comercial'] ?? ($campana->tenant->nombre_comercial ?? config('app.name'));
                    $telefonoContacto = $contacto['telefono'] ?? null;
                    $emailContacto = $contacto['email'] ?? null;
                @endphp
                Este mensaje fue enviado por {{ $nombreComercial }}.<br>
                @if($telefonoContacto || $emailContacto)
                    Si no deseas recibir estas comunicaciones, comunícate con {{ $nombreComercial }}
                    @if($telefonoContacto)
                        al {{ $telefonoContacto }}
                    @endif
                    @if($emailContacto)
                        @if($telefonoContacto) o @endif
                        a {{ $emailContacto }}
                    @endif
                    .
                @else
                    Si no deseas recibir estas comunicaciones, comunícate con el comercio.
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
