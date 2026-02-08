<?php
session_start();
date_default_timezone_set('America/Guayaquil');
include_once '../../models/cierreCajaModel.php';
include_once '../../models/loginModel.php';

// Verificar sesión activa
if (!isset($_SESSION['email'])) {
  echo json_encode(['error' => 'Sesión no válida']);
  exit;
}

// Obtener información del usuario
foreach (LoginModel::existeUsuarioEmailLogin($_SESSION['email']) as $key => $value) {
  $usuario_id = $value['usuario_id'];
}

$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');

try {
  // Obtener todos los datos necesarios para el cierre
  $totalesDia = CierreCajaModel::calcularTotalesDia($fecha);
  $ventasPorTipo = CierreCajaModel::obtenerVentasPorTipoPago($fecha);
  $movimientos = CierreCajaModel::obtenerMovimientosDia($fecha);
  $saldoInicial = CierreCajaModel::calcularSaldoInicial($fecha);

  // Verificar si ya existe un cierre para esta fecha (solo para información)
  $existeCierre = CierreCajaModel::existeCierreFecha($fecha);

  // Preparar respuesta
  $response = [
    'success' => true,
    'fecha' => $fecha,
    'cierre_existe' => $existeCierre ? true : false,
    'mensaje_cierre' => $existeCierre ? 'Ya existe un cierre para esta fecha' : null,
    'saldo_inicial' => number_format($saldoInicial, 2),
    'totales' => $totalesDia,
    'ventas_por_tipo' => $ventasPorTipo,
    'movimientos' => $movimientos,
    'resumen' => [
      'total_ingresos_efectivo' => number_format($totalesDia['ventas_efectivo'], 2),
      'total_ingresos_transferencia' => number_format($totalesDia['ventas_transferencia'], 2),
      'total_ingresos_cheque' => number_format($totalesDia['ventas_cheque'], 2),
      'total_ingresos_otros' => number_format($totalesDia['ventas_otros'], 2),
      'total_egresos' => number_format($totalesDia['total_egresos_mov'] ?? 0, 2),
      'total_ingresos_mov' => number_format($totalesDia['total_ingresos_mov'] ?? 0, 2),
      'ventas_sin_iva' => number_format($totalesDia['ventas_sin_iva'], 2),
      'ventas_con_iva' => number_format($totalesDia['ventas_con_iva'], 2),
      'iva_cobrado' => number_format($totalesDia['total_impuesto'], 2),
      'total_ventas' => number_format($totalesDia['total_ventas'], 2),
      'saldo_final' => number_format(
        floatval($saldoInicial) +
          floatval($totalesDia['ventas_efectivo'] ?? 0) +
          floatval($totalesDia['total_ingresos_mov'] ?? 0) -
          floatval($totalesDia['total_egresos_mov'] ?? 0),
        2
      )
    ]
  ];

  echo json_encode($response);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Error al obtener datos: ' . $e->getMessage()
  ]);
}
