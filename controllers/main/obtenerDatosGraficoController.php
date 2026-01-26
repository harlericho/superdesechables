<?php
include_once '../../models/facturaModel.php';
session_start();

// Array de nombres de meses en español
$mesesEsp = array(
  '01' => 'Ene',
  '02' => 'Feb',
  '03' => 'Mar',
  '04' => 'Abr',
  '05' => 'May',
  '06' => 'Jun',
  '07' => 'Jul',
  '08' => 'Ago',
  '09' => 'Sep',
  '10' => 'Oct',
  '11' => 'Nov',
  '12' => 'Dic'
);

// Generar array con los últimos 12 meses
$datosGrafico = array();
for ($i = 11; $i >= 0; $i--) {
  $timestamp = strtotime("-$i months");
  $periodo = date('Y-m', $timestamp);
  $mes = date('m', $timestamp);
  $anio = date('Y', $timestamp);

  $datosGrafico[$periodo] = array(
    'y' => $mesesEsp[$mes] . ' ' . $anio,
    'ventas' => 0.0,
    'cantidad' => 0
  );
}

// Obtener datos de ventas mensuales de la base de datos
$ventasMensuales = FacturaModel::obtenerVentasMensuales();

// Rellenar con los datos reales donde existan
foreach ($ventasMensuales as $venta) {
  $periodo = $venta['periodo'];

  // Solo agregar si el período está en nuestro rango de 12 meses
  if (isset($datosGrafico[$periodo])) {
    $datosGrafico[$periodo]['ventas'] = (float)round($venta['total_ventas'], 2);
    $datosGrafico[$periodo]['cantidad'] = (int)$venta['cantidad_ventas'];
  }
}

// Convertir a array indexado (sin las claves de período)
$datosGrafico = array_values($datosGrafico);

echo json_encode($datosGrafico);
