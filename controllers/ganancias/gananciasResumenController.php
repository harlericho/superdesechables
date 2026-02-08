<?php
header('Content-Type: application/json; charset=utf-8');
include_once '../../models/gananciasModel.php';

$fechaDesde = isset($_POST['fecha_desde']) && !empty($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null;
$fechaHasta = isset($_POST['fecha_hasta']) && !empty($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null;
$codigoProducto = isset($_POST['codigo_producto']) && !empty($_POST['codigo_producto']) ? $_POST['codigo_producto'] : null;

try {
  $resumen = GananciasModel::obtenerResumenGanancias($fechaDesde, $fechaHasta, $codigoProducto);
  error_log("Resumen ganancias: " . json_encode($resumen));
  echo json_encode($resumen);
} catch (Exception $e) {
  error_log("Error en gananciasResumenController: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
