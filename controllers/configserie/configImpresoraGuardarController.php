<?php
require_once '../../config/session.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['status' => 0, 'message' => 'Método no permitido']);
  exit;
}

$impresora = isset($_POST['impresora_ticket']) ? trim($_POST['impresora_ticket']) : '';

// Validar que no esté vacío y no contenga caracteres peligrosos
if ($impresora === '' || !preg_match('/^[\w\s\-]+$/', $impresora)) {
  echo json_encode(['status' => 0, 'message' => 'Nombre de impresora inválido']);
  exit;
}

$configPath = dirname(__DIR__, 2) . '/empresa.ini';

if (!file_exists($configPath)) {
  echo json_encode(['status' => 0, 'message' => 'Archivo de configuración no encontrado']);
  exit;
}

// Leer el contenido actual del archivo
$contenido = file_get_contents($configPath);

// Reemplazar la línea impresora_ticket existente o añadirla antes del cierre de sección
if (preg_match('/^impresora_ticket\s*=.*$/m', $contenido)) {
  $contenido = preg_replace(
    '/^impresora_ticket\s*=.*$/m',
    'impresora_ticket = "' . $impresora . '" ; Nombre de la impresora para tickets POS',
    $contenido
  );
} else {
  // Añadir al final de la sección [empresa]
  $contenido .= "\nimpresora_ticket = \"" . $impresora . "\" ; Nombre de la impresora para tickets POS\n";
}

if (file_put_contents($configPath, $contenido) !== false) {
  echo json_encode(['status' => 1, 'message' => 'Impresora guardada correctamente']);
} else {
  echo json_encode(['status' => 0, 'message' => 'No se pudo guardar la configuración']);
}
