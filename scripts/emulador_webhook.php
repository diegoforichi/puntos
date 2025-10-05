<?php
/**
 * Emulador de Webhook - Sistema de eFactura
 * 
 * Simula el envío de facturas desde un sistema de eFactura al webhook de Laravel.
 * Lee el JSON de referencia y genera datos aleatorios para pruebas.
 * 
 * Uso:
 *   php scripts/emulador_webhook.php --url=http://localhost:8000/api/webhook/ingest --rut=000000000016 --api-key=test123
 * 
 * Opciones:
 *   --url            URL del webhook (default: http://localhost:8000/api/webhook/ingest)
 *   --rut            RUT del emisor/tenant (default: 000000000016)
 *   --api-key        API Key del tenant (default: test-api-key-demo)
 *   --cantidad       Número de facturas a generar (default: 1)
 *   --sin-telefono   Simular cliente sin teléfono
 *   --rut-incorrecto Enviar con RUT que no existe
 *   --api-key-mala   Enviar con API Key incorrecta
 *   --help           Mostrar esta ayuda
 */

// Configuración por defecto
$config = [
    'url' => 'http://localhost:8000/api/webhook/ingest',
    'rut' => '000000000016',
    'api_key' => 'test-api-key-demo',
    'cantidad' => 1,
    'sin_telefono' => false,
    'rut_incorrecto' => false,
    'api_key_mala' => false,
    'cfeid' => null,
    'doc_mode' => 'random',
    'monto' => null,
];

// Parsear argumentos de línea de comandos
parseArgs($config);

// Mostrar ayuda si se solicita
if (isset($config['help'])) {
    mostrarAyuda();
    exit(0);
}

// Validar que existe el archivo JSON de referencia
$jsonFile = __DIR__ . '/hookCfe.json';
if (!file_exists($jsonFile)) {
    echo "❌ Error: No se encontró el archivo scripts/hookCfe.json.\n";
    exit(1);
}

// Cargar JSON de referencia
$jsonTemplate = json_decode(file_get_contents($jsonFile), true);
if (!$jsonTemplate) {
    echo "❌ Error: El archivo hookCfe.json no es un JSON válido.\n";
    exit(1);
}

echo "\n🚀 Emulador de Webhook - Sistema de Puntos\n";
echo str_repeat("=", 60) . "\n\n";
echo "📡 URL destino: {$config['url']}\n";
echo "🏢 RUT Emisor: {$config['rut']}\n";
echo "🔑 API Key: " . substr($config['api_key'], 0, 8) . "...\n";
echo "📊 Cantidad: {$config['cantidad']} factura(s)\n\n";

if ($config['sin_telefono']) echo "⚠️  Modo: Cliente SIN teléfono\n";
if ($config['rut_incorrecto']) echo "⚠️  Modo: RUT INCORRECTO\n";
if ($config['api_key_mala']) echo "⚠️  Modo: API Key INCORRECTA\n";
    if ($config['cfeid']) {
    echo "⚙️  CFE Id: {$config['cfeid']}\n";
}
if ($config['doc_mode'] !== 'random') {
    echo "⚙️  Documento forzado: {$config['doc_mode']}\n";
}
if ($config['monto']) {
    echo "⚙️  Monto fijo: {$config['monto']}\n";
}
    if (!empty($config['moneda'])) {
        echo "⚙️  Moneda forzada: {$config['moneda']}\n";
    }

echo str_repeat("-", 60) . "\n\n";

// Generar y enviar facturas
$exitosos = 0;
$fallidos = 0;

for ($i = 1; $i <= $config['cantidad']; $i++) {
    echo "📄 Generando factura #{$i}...\n";
    
    $factura = generarFactura($jsonTemplate, $config);
    $resultado = enviarWebhook($factura, $config);
    
    if ($resultado['exitoso']) {
        $exitosos++;
        $puntos = $resultado['respuesta_json']['puntos_generados'] ?? 'desconocido';
        echo "✅ Factura #{$i} enviada correctamente\n";
        echo "   Número: {$factura['Numero']} | Cliente: {$factura['Client']['NroDoc']} | Monto: \${$factura['Totales']['TotMntTotal']} | Puntos: {$puntos}\n";
    } else {
        $fallidos++;
        echo "❌ Error al enviar factura #{$i}\n";
        echo "   Código HTTP: {$resultado['codigo']}\n";
        echo "   Respuesta: {$resultado['respuesta']}\n";
    }
    
    echo "\n";
    
    // Pausa entre envíos si hay múltiples facturas
    if ($i < $config['cantidad']) {
        sleep(1);
    }
}

// Resumen final
echo str_repeat("=", 60) . "\n";
echo "📊 Resumen:\n";
echo "   ✅ Exitosos: {$exitosos}\n";
echo "   ❌ Fallidos: {$fallidos}\n";
echo str_repeat("=", 60) . "\n\n";

exit($fallidos > 0 ? 1 : 0);

// ============================================================================
// FUNCIONES AUXILIARES
// ============================================================================

/**
 * Genera una factura con datos aleatorios basada en el template
 */
function generarFactura($template, $config) {
    $factura = $template;
    
    // Generar datos aleatorios
    $numeroFactura = 'F' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $factura['Numero'] = $numeroFactura;
    $factura['FechaEmision'] = date('Y-m-d\TH:i:s') . '-03:00';
    
    // Cliente aleatorio
    $nombres = ['Juan', 'María', 'Pedro', 'Ana', 'Carlos', 'Lucía', 'Diego', 'Sofía'];
    $apellidos = ['González', 'Rodríguez', 'Pérez', 'Martínez', 'García', 'López', 'Fernández', 'Sánchez'];
    
    if ($config['doc_mode'] === 'ci') {
        $factura['Client']['NroDoc'] = sprintf('%07d', rand(1000000, 9999999));
        $factura['Client']['CodigoTipoDocumento'] = 'CI';
        $factura['Client']['TipoDocumento'] = 'Cédula de Identidad';
    } elseif ($config['doc_mode'] === 'rut') {
        $factura['Client']['NroDoc'] = generarRutChileno();
        $factura['Client']['CodigoTipoDocumento'] = 'RUT';
        $factura['Client']['TipoDocumento'] = 'Rol Único Tributario';
    } else {
        if (rand(0, 1)) {
            $factura['Client']['NroDoc'] = generarRutChileno();
            $factura['Client']['CodigoTipoDocumento'] = 'RUT';
            $factura['Client']['TipoDocumento'] = 'Rol Único Tributario';
        } else {
            $factura['Client']['NroDoc'] = sprintf('%07d', rand(1000000, 9999999));
            $factura['Client']['CodigoTipoDocumento'] = 'CI';
            $factura['Client']['TipoDocumento'] = 'Cédula de Identidad';
        }
    }
    
    $factura['Client']['Nombre'] = $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)];
    
    // Teléfono (opcional según flag)
    if ($config['sin_telefono']) {
        $factura['Client']['Telefono'] = null;
    } else {
        $factura['Client']['Telefono'] = '+569' . rand(1000000, 9999999);
    }
    
    // Email aleatorio (50% de probabilidad)
    if (rand(0, 1)) {
        $factura['Client']['Email'] = strtolower(str_replace(' ', '', $factura['Client']['Nombre'])) . '@test.com';
    }
    
    // RUT del emisor (puede ser incorrecto según flag)
    if ($config['rut_incorrecto']) {
        $factura['Emisor']['RUT'] = '99999999-9'; // RUT que no existe
    } else {
        $factura['Emisor']['RUT'] = $config['rut'];
    }
    
    // CFE Id (opcional según flag)
    if ($config['cfeid']) {
        $factura['CfeId'] = (int) $config['cfeid'];
    } else {
        $factura['CfeId'] = [101, 102, 111, 112, 113][array_rand([101, 102, 111, 112, 113])];
    }
    
    // Monto total aleatorio
    if ($config['monto']) {
        $montoBase = (int) $config['monto'];
    } else {
        $montoBase = rand(5000, 100000);
    }
    $iva = round($montoBase * 0.19, 0);
    $montoTotal = $montoBase + $iva;
    
    $factura['Totales']['MntNeto'] = $montoBase;
    $factura['Totales']['IVA'] = $iva;
    $factura['Totales']['TotMntTotal'] = $montoTotal;

    if (isset($config['moneda']) && $config['moneda']) {
        $factura['Totales']['TpoMoneda'] = strtoupper($config['moneda']);
    }

    if (isset($config['moneda']) && $config['moneda']) {
        $factura['Totales']['TpoMoneda'] = strtoupper($config['moneda']);
    }
    
    // Detalle de items
    $productos = [
        'Producto de Prueba',
        'Servicio Adicional',
        'Artículo Variado',
        'Mercancía General',
        'Bien de Consumo',
        'Producto Estándar'
    ];
    
    $factura['Detalle'][0]['NmbItem'] = $productos[array_rand($productos)];
    $factura['Detalle'][0]['QtyItem'] = rand(1, 3);
    $factura['Detalle'][0]['PrcItem'] = round($montoTotal / $factura['Detalle'][0]['QtyItem'], 0);
    $factura['Detalle'][0]['MontoItem'] = $montoTotal;
    
    // Actualizar folio
    $factura['IdDoc']['Folio'] = rand(10000, 99999);
    $factura['Encabezado']['IdDoc']['Folio'] = $factura['IdDoc']['Folio'];
    
    return $factura;
}

/**
 * Genera un RUT chileno aleatorio
 */
function generarRutChileno() {
    // Generar número aleatorio de 7-8 dígitos
    $numero = rand(1000000, 99999999);
    
    // Calcular dígito verificador
    $dv = calcularDigitoVerificador($numero);
    
    return $numero . '-' . $dv;
}

/**
 * Calcula el dígito verificador de un RUT chileno
 */
function calcularDigitoVerificador($rut) {
    $rut = str_pad($rut, 8, '0', STR_PAD_LEFT);
    $suma = 0;
    $multiplicador = 2;
    
    for ($i = 7; $i >= 0; $i--) {
        $suma += intval($rut[$i]) * $multiplicador;
        $multiplicador = $multiplicador == 7 ? 2 : $multiplicador + 1;
    }
    
    $resto = $suma % 11;
    $dv = 11 - $resto;
    
    if ($dv == 11) return '0';
    if ($dv == 10) return 'K';
    return (string)$dv;
}

/**
 * Envía el webhook via HTTP POST
 */
function enviarWebhook($factura, $config) {
    $url = $config['url'];
    $apiKey = $config['api_key_mala'] ? 'api-key-incorrecta-para-prueba' : $config['api_key'];
    
    $json = json_encode($factura, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    // Preparar headers
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'Accept: application/json',
    ];
    
    // Configurar cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Ejecutar petición
    $respuesta = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $respuestaJson = json_decode($respuesta, true);

    // Verificar resultado
    if ($error) {
        return [
            'exitoso' => false,
            'codigo' => 0,
            'respuesta' => "Error de conexión: {$error}",
        ];
    }
    
    return [
        'exitoso' => ($httpCode >= 200 && $httpCode < 300),
        'codigo' => $httpCode,
        'respuesta' => $respuesta ?: 'Sin respuesta',
        'respuesta_json' => $respuestaJson,
    ];
}

/**
 * Parsea argumentos de línea de comandos
 */
function parseArgs(&$config) {
    global $argv;
    
    if (!isset($argv)) return;
    
    foreach ($argv as $arg) {
        if (strpos($arg, '--') !== 0) continue;
        
        $arg = substr($arg, 2); // Quitar --
        
        if ($arg === 'help') {
            $config['help'] = true;
            continue;
        }
        
        if (strpos($arg, '=') !== false) {
            list($key, $value) = explode('=', $arg, 2);
            $key = str_replace('-', '_', $key);
            $config[$key] = $value;
        } else {
            // Flags sin valor (booleanos)
            $config[str_replace('-', '_', $arg)] = true;
        }
    }
}

/**
 * Muestra la ayuda del script
 */
function mostrarAyuda() {
    echo "\n";
    echo "🚀 Emulador de Webhook - Sistema de Puntos\n";
    echo str_repeat("=", 60) . "\n\n";
    echo "Uso:\n";
    echo "  php scripts/emulador_webhook.php [opciones]\n\n";
    echo "Opciones:\n";
    echo "  --url=URL            URL del webhook\n";
    echo "                       (default: http://localhost:8000/api/webhook/ingest)\n\n";
    echo "  --rut=RUT            RUT del emisor/tenant\n";
    echo "                       (default: 000000000016)\n\n";
    echo "  --api-key=KEY        API Key del tenant\n";
    echo "                       (default: test-api-key-demo)\n\n";
    echo "  --cantidad=N         Número de facturas a generar\n";
    echo "                       (default: 1)\n\n";
    echo "  --sin-telefono       Simular cliente sin teléfono\n";
    echo "  --rut-incorrecto     Enviar con RUT que no existe\n";
    echo "  --api-key-mala       Enviar con API Key incorrecta\n";
    echo "  --cfeid=NNN          Forzar tipo de comprobante (101,102,111,112,113)\n";
    echo "  --doc-mode=ci|rut    Forzar tipo de documento del cliente\n";
    echo "  --moneda=ISO         Forzar moneda (ej. USD, UYU)\n";
    echo "  --monto=NNN          Monto base fijo para pruebas\n";
    echo "  --help               Mostrar esta ayuda\n\n";
    echo "Ejemplos:\n";
    echo "  # Enviar 1 factura al webhook local\n";
    echo "  php scripts/emulador_webhook.php\n\n";
    echo "  # Enviar 5 facturas\n";
    echo "  php scripts/emulador_webhook.php --cantidad=5\n\n";
    echo "  # Probar con cliente sin teléfono\n";
    echo "  php scripts/emulador_webhook.php --sin-telefono\n\n";
    echo "  # Probar con API Key incorrecta\n";
    echo "  php scripts/emulador_webhook.php --api-key-mala\n\n";
    echo "  # Enviar a servidor remoto\n";
    echo "  php scripts/emulador_webhook.php --url=https://midominio.com/api/webhook/ingest --api-key=tu-api-key-real\n\n";
    echo str_repeat("=", 60) . "\n\n";
}
