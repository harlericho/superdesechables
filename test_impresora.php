<?php
require_once 'vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

echo "<h2>Test de Detección de Impresora POS</h2>";

// Método 1: Listar impresoras con PowerShell
echo "<h3>1. Impresoras instaladas en Windows:</h3>";
$output = [];
exec('powershell -Command "Get-Printer | Select-Object Name, DriverName, PortName | Format-List"', $output);
echo "<pre>" . implode("\n", $output) . "</pre>";

// Método 2: Intentar conectar con WindowsPrintConnector
echo "<h3>2. Prueba de conexión con WindowsPrintConnector:</h3>";

$nombreImpresoras = ['XP-80C', 'XP-80T', 'Xprinter XP-80C', 'Xprinter XP-80T'];

foreach ($nombreImpresoras as $nombre) {
  echo "<p>Intentando conectar a: <strong>$nombre</strong>... ";
  try {
    $connector = new WindowsPrintConnector($nombre);
    $printer = new Printer($connector);
    $printer->text("Test\n");
    $printer->close();
    echo "<span style='color:green'>✓ CONECTADA</span></p>";
  } catch (Exception $e) {
    echo "<span style='color:red'>✗ Error: " . $e->getMessage() . "</span></p>";
  }
}

// Método 3: Probar impresión directa con PowerShell
echo "<h3>3. Prueba de impresión con formato ESC/POS:</h3>";

// Crear ticket de prueba con comandos ESC/POS
$ticket = "";
$ticket .= "\x1B\x40"; // ESC @ - Initialize
$ticket .= "\x1B\x61\x00"; // ESC a 0 - Align LEFT

// Texto en negrita
$ticket .= "\x1B\x45\x01"; // ESC E 1 - Bold ON
$ticket .= "PRUEBA DE IMPRESORA POS XP-80C\n";
$ticket .= "\x1B\x45\x00"; // ESC E 0 - Bold OFF
$ticket .= "\n";

// Texto alineado a la izquierda ocupando todo el ancho
$ticket .= str_pad("Item", 14, ' ', STR_PAD_RIGHT);
$ticket .= str_pad("Precio", 7, ' ', STR_PAD_LEFT);
$ticket .= str_pad("Cant", 4, ' ', STR_PAD_LEFT);
$ticket .= str_pad("Total", 7, ' ', STR_PAD_LEFT);
$ticket .= "\n";
$ticket .= str_repeat('-', 40) . "\n";

// Productos de ejemplo
$ticket .= str_pad("Producto A", 14, ' ', STR_PAD_RIGHT);
$ticket .= str_pad("$10.50", 7, ' ', STR_PAD_LEFT);
$ticket .= str_pad("x2", 4, ' ', STR_PAD_LEFT);
$ticket .= str_pad("$21.00", 7, ' ', STR_PAD_LEFT);
$ticket .= "\n";

$ticket .= str_pad("Producto B", 14, ' ', STR_PAD_RIGHT);
$ticket .= str_pad("$5.75", 7, ' ', STR_PAD_LEFT);
$ticket .= str_pad("x1", 4, ' ', STR_PAD_LEFT);
$ticket .= str_pad("$5.75", 7, ' ', STR_PAD_LEFT);
$ticket .= "\n";

$ticket .= str_repeat('-', 40) . "\n";

// Total en negrita
$ticket .= "\x1B\x45\x01"; // Bold ON
$ticket .= str_pad("TOTAL:", 20, ' ', STR_PAD_RIGHT);
$ticket .= str_pad("$26.75", 20, ' ', STR_PAD_LEFT);
$ticket .= "\n";
$ticket .= "\x1B\x45\x00"; // Bold OFF

$ticket .= "\n\n";
$ticket .= "\x1D\x56\x00"; // GS V 0 - Cut paper

// Guardar archivo temporal
$tempFile = sys_get_temp_dir() . '\\test_ticket.prn';
file_put_contents($tempFile, $ticket);

echo "<p><strong>Archivo temporal:</strong> $tempFile</p>";
echo "<p><strong>Tamaño del archivo:</strong> " . filesize($tempFile) . " bytes</p>";

// Usar el Método 2 que funcionó: COPY /B a USB001
echo "<h3>Imprimiendo con COPY /B directamente a impresora compartida...</h3>";
$command = 'copy /B "' . $tempFile . '" "\\\\' . gethostname() . '\\XP-80C" 2>&1';
exec($command, $output, $returnCode);

echo "<p><strong>Comando:</strong> <code>$command</code></p>";
echo "<p><strong>Código de retorno:</strong> $returnCode</p>";
if (!empty($output)) {
  echo "<p><strong>Output:</strong> <code>" . implode('<br>', $output) . "</code></p>";
}

if ($returnCode === 0) {
  echo "<div style='background:green;color:white;padding:10px;margin:10px 0;'>";
  echo "<strong>✓ ARCHIVO ENVIADO A LA IMPRESORA</strong>";
  echo "</div>";
  echo "<p>Revisa la impresora físicamente. El ticket debe tener:</p>";
  echo "<ul>";
  echo "<li>✓ Texto alineado a la IZQUIERDA (no centrado)</li>";
  echo "<li>✓ Tabla ocupando TODO el ancho (40 caracteres)</li>";
  echo "<li>✓ \"PRUEBA DE IMPRESORA POS\" en NEGRITA</li>";
  echo "<li>✓ \"TOTAL:\" en NEGRITA</li>";
  echo "</ul>";
} else {
  echo "<div style='background:red;color:white;padding:10px;'>";
  echo "<strong>✗ ERROR AL ENVIAR A LA IMPRESORA</strong>";
  echo "</div>";
}

sleep(1);
@unlink($tempFile);

echo "<hr>";
echo "<p><strong>Si el ticket imprimió alineado a la izquierda con el ancho completo y texto en negrita, el sistema está funcionando correctamente.</strong></p>";
