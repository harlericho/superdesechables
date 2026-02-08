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

try {
  // Validar datos requeridos
  $fecha = $_POST['fecha'] ?? date('Y-m-d');
  $observaciones = $_POST['observaciones'] ?? '';

  // Validar que las observaciones no estén vacías
  if (empty(trim($observaciones))) {
    echo json_encode([
      'success' => false,
      'message' => 'Las observaciones son requeridas para realizar el cierre'
    ]);
    exit;
  }

  // Validar longitud mínima de observaciones
  $observaciones = trim($observaciones);
  if (strlen($observaciones) < 5) {
    echo json_encode([
      'success' => false,
      'message' => 'Las observaciones deben tener al menos 5 caracteres'
    ]);
    exit;
  }

  // Verificar si ya existe un cierre para esta fecha
  $existeCierre = CierreCajaModel::existeCierreFecha($fecha);

  if ($existeCierre) {
    echo json_encode([
      'success' => false,
      'message' => 'PROHIBIDO: Ya existe un cierre de caja para la fecha: ' . $fecha . '. Solo se permite un cierre por día.'
    ]);
    exit;
  }

  // Obtener datos del día
  $totalesDia = CierreCajaModel::calcularTotalesDia($fecha);
  $saldoInicial = CierreCajaModel::calcularSaldoInicial($fecha);

  // Verificar que los valores no sean null y convertir a float
  $ventasEfectivo = isset($totalesDia['ventas_efectivo']) ? floatval($totalesDia['ventas_efectivo']) : 0.00;
  $ventasTransferencia = isset($totalesDia['ventas_transferencia']) ? floatval($totalesDia['ventas_transferencia']) : 0.00;
  $ventasCheque = isset($totalesDia['ventas_cheque']) ? floatval($totalesDia['ventas_cheque']) : 0.00;
  $ventasOtros = isset($totalesDia['ventas_otros']) ? floatval($totalesDia['ventas_otros']) : 0.00;
  $totalIngresos = isset($totalesDia['total_ingresos_mov']) ? floatval($totalesDia['total_ingresos_mov']) : 0.00;
  $totalEgresos = isset($totalesDia['total_egresos_mov']) ? floatval($totalesDia['total_egresos_mov']) : 0.00;
  $ventasSinIva = isset($totalesDia['ventas_sin_iva']) ? floatval($totalesDia['ventas_sin_iva']) : 0.00;
  $ventasConIva = isset($totalesDia['ventas_con_iva']) ? floatval($totalesDia['ventas_con_iva']) : 0.00;
  $ivaTotal = isset($totalesDia['total_impuesto']) ? floatval($totalesDia['total_impuesto']) : 0.00;

  // Calcular saldo final (solo efectivo)
  $saldoFinal = floatval($saldoInicial) + $ventasEfectivo + $totalIngresos - $totalEgresos;

  // Preparar datos para el cierre
  $datoCierre = [
    'cierre_fecha' => $fecha,
    'cierre_hora' => date('H:i:s'),
    'cierre_saldo_inicial' => $saldoInicial,
    'cierre_ingresos_efectivo' => $ventasEfectivo,
    'cierre_ingresos_transferencia' => $ventasTransferencia,
    'cierre_ingresos_cheque' => $ventasCheque,
    'cierre_ingresos_otros' => $ventasOtros,
    'cierre_total_ingresos' => $totalIngresos,
    'cierre_total_egresos' => $totalEgresos,
    'cierre_ventas_sin_iva' => $ventasSinIva,
    'cierre_ventas_con_iva' => $ventasConIva,
    'cierre_iva_cobrado' => $ivaTotal,
    'cierre_saldo_final' => $saldoFinal,
    'cierre_observaciones' => $observaciones,
    'cierre_usuario_id' => $usuario_id
  ];

  // Guardar el cierre principal
  $cierreId = CierreCajaModel::guardarCierreCaja($datoCierre);

  if ($cierreId) {
    // Preparar detalles del cierre
    $detalles = [];

    // Agregar detalles de ventas por tipo de pago
    $ventasPorTipo = CierreCajaModel::obtenerVentasPorTipoPago($fecha);
    foreach ($ventasPorTipo as $venta) {
      $detalles[] = [
        'cierre_id' => $cierreId,
        'detalle_tipo' => 'VENTA',
        'detalle_concepto' => 'Ventas por ' . $venta['tipo_pago'],
        'detalle_monto' => $venta['total_ventas'],
        'detalle_tipo_pago' => $venta['tipo_pago'],
        'detalle_porcentaje_iva' => 0 // Se podría calcular dinámicamente
      ];
    }

    // Agregar detalles de movimientos de caja
    $movimientos = CierreCajaModel::obtenerMovimientosDia($fecha);

    foreach ($movimientos as $mov) {
      $detalles[] = [
        'cierre_id' => $cierreId,
        'detalle_tipo' => $mov['mov_tipo'],
        'detalle_concepto' => 'Movimientos de caja - ' . $mov['mov_tipo'],
        'detalle_monto' => $mov['total_monto'],
        'detalle_tipo_pago' => 'EFECTIVO',
        'detalle_porcentaje_iva' => 0
      ];
    }

    // Intentar guardar detalles (opcional)
    $detallesGuardados = CierreCajaModel::guardarDetalleCierre($detalles);

    // Si el cierre principal se guardó, consideramos el proceso exitoso
    echo json_encode([
      'success' => true,
      'message' => 'Cierre de caja realizado exitosamente',
      'cierre_id' => $cierreId,
      'fecha' => $fecha,
      'saldo_final' => number_format($saldoFinal, 2)
    ]);
  } else {
    echo json_encode([
      'success' => false,
      'message' => 'Error al guardar el cierre de caja. Sin embargo, verifica si se guardó correctamente.',
      'debug' => 'Cierre ID devuelto: ' . var_export($cierreId, true)
    ]);
  }
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Error del sistema al procesar el cierre'
  ]);
}
