<?php
// controllers/facturacion_electronica/feReintentarController.php
header('Content-Type: application/json');

require_once '../../config/db.php';
require_once '../../helpers/facturacion_electronica_service.php';

try {
  if (!isset($_POST['factura_id']) || empty($_POST['factura_id'])) {
    echo json_encode(['status' => 0, 'message' => 'Falta el ID de la factura']);
    exit;
  }

  $facturaId = intval($_POST['factura_id']);
  $usuarioId = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 1; // Podría venir de la sesión

  // Llamar al servicio de reintento
  $resultado = FacturacionElectronicaService::reintentarFactura($facturaId, $usuarioId);

  if ($resultado['success'] && $resultado['autorizado']) {
    // Si fue autorizada, intentar enviar el PDF y XML por correo automáticamente
    // Para ello llamamos a otro script interno o lo hacemos mediante curl local,
    // o simplemente incluimos facturaPdfController y la enviamos.
    
    // Obtener correo del cliente para reenvío automático
    $sql = "SELECT c.cliente_email FROM tbl_factura f INNER JOIN tbl_cliente c ON f.cliente_id = c.cliente_id WHERE f.factura_id = :fid";
    $stmt = Db::dbConnection()->prepare($sql);
    $stmt->execute([':fid' => $facturaId]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    $correoCliente = $cliente ? $cliente['cliente_email'] : '';

    // Construir la URL dinámicamente para que funcione tanto en localhost como en producción
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $requestUri = $_SERVER['REQUEST_URI'];
    $baseUri = explode('/controllers/facturacion_electronica', $requestUri)[0];
    
    $url = $protocol . "://" . $host . $baseUri . "/controllers/factura/facturaReenviarCorreoController.php";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
      'factura_id' => $facturaId,
      'correo' => $correoCliente
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 segundos max
    curl_exec($ch);
    curl_close($ch);

    echo json_encode([
      'status' => 1,
      'message' => 'Factura reintentada y autorizada correctamente'
    ]);
  } else {
    // No autorizada (o error)
    $mensaje = $resultado['error'] ?? ($resultado['estado_sri'] . ' - ' . ($resultado['mensaje'] ?? 'Error desconocido'));
    echo json_encode([
      'status' => 0,
      'message' => $mensaje
    ]);
  }

} catch (Exception $e) {
  echo json_encode([
    'status' => 0,
    'message' => 'Excepción: ' . $e->getMessage()
  ]);
}
