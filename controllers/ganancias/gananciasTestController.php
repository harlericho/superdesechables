<?php
header('Content-Type: application/json; charset=utf-8');
include_once '../../config/db.php';

try {
  // Test básico de conexión
  $conexion = Db::dbConnection();

  // Consulta super simple para probar
  $sql = "SELECT COUNT(*) as total FROM tbl_detalle";
  $query = $conexion->prepare($sql);
  $query->execute();
  $result = $query->fetch(PDO::FETCH_ASSOC);

  echo json_encode([
    'status' => 'ok',
    'total_detalles' => $result['total'],
    'mensaje' => 'Conexión exitosa'
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'error' => $e->getMessage(),
    'linea' => $e->getLine(),
    'archivo' => $e->getFile()
  ]);
}
