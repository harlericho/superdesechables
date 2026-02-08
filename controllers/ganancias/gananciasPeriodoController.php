<?php
header('Content-Type: application/json; charset=utf-8');
include_once '../../models/gananciasModel.php';

$periodo = isset($_POST['periodo']) ? $_POST['periodo'] : 'diario';
$fechaDesde = isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null;
$fechaHasta = isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null;

try {
  $ganancias = GananciasModel::obtenerGananciasPorPeriodo($periodo, $fechaDesde, $fechaHasta);
  error_log("Período '$periodo': " . count($ganancias) . " periodos encontrados");
  echo json_encode($ganancias);
} catch (Exception $e) {
  error_log("Error en gananciasPeriodoController: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
