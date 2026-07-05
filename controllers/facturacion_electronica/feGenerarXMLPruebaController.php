<?php

/**
 * Controlador: Generar XML de prueba
 */

require_once '../../config/db.php';
include_once '../../models/facturacionElectronicaModel.php';
include_once '../../helpers/facturacion_electronica_helper.php';

header('Content-Type: application/json');

try {
  // Obtener configuración
  $config = FacturacionElectronicaModel::obtenerConfiguracion();

  if (!$config) {
    echo json_encode([
      'status' => 0,
      'message' => 'No hay configuración disponible'
    ]);
    exit;
  }

  // Generar datos de prueba
  $fecha = date('dmY'); // 09022026
  $tipoComprobante = '01'; // Factura
  $ruc = $config['config_fe_ruc'];
  $ambiente = $config['config_fe_ambiente'] == 'PRUEBAS' ? '1' : '2';
  $establecimiento = str_pad($config['config_fe_cod_establecimiento'], 3, '0', STR_PAD_LEFT);
  $puntoEmision = str_pad($config['config_fe_cod_punto_emision'], 3, '0', STR_PAD_LEFT);
  $serie = $establecimiento . $puntoEmision;
  $secuencial = str_pad(1, 9, '0', STR_PAD_LEFT); // Secuencial de prueba
  $codigoNumerico = FacturacionElectronicaHelper::generarCodigoNumerico();
  $tipoEmision = $config['config_fe_tipo_emision'] == 'NORMAL' ? '1' : '2';

  // Generar clave de acceso
  $claveAcceso = FacturacionElectronicaHelper::generarClaveAcceso(
    $fecha,
    $tipoComprobante,
    $ruc,
    $ambiente,
    $serie,
    $secuencial,
    $codigoNumerico,
    $tipoEmision
  );

  // Datos de factura de prueba
  $datosFactura = [
    'fe_tipo_comprobante' => $tipoComprobante,
    'fe_clave_acceso' => $claveAcceso,
    'fe_secuencial' => $secuencial,
    'factura_fecha' => date('Y-m-d'),
    'fe_cliente_tipo_identificacion' => '07',
    'fe_cliente_identificacion' => '9999999999999',
    'fe_cliente_razon_social' => 'CONSUMIDOR FINAL',
    'fe_cliente_direccion' => 'Ecuador',
    'fe_subtotal_sin_impuestos' => 10.00,
    'fe_subtotal_iva0' => 0.00,
    'fe_subtotal_iva' => 10.00,
    'fe_iva_valor' => 1.50, // 15% IVA
    'fe_descuento_total' => 0.00,
    'fe_propina' => 0.00,
    'fe_total_comprobante' => 11.50,
    'detalles' => [
      [
        'producto_codigo' => 'PROD001',
        'producto_nombre' => 'Producto de Prueba',
        'detalle_cantidad' => 1,
        'detalle_precio_unit' => 10.00,
        'detalle_descuento' => 0,
        'precio_sin_impuesto' => 10.00,
        'codigo_porcentaje_iva' => '4', // 15%
        'tarifa_iva' => 15,
        'valor_iva' => 1.50
      ]
    ],
    'informacion_adicional' => [
      'Email' => 'prueba@ejemplo.com',
      'Teléfono' => '0999999999'
    ]
  ];

  // Generar XML
  $xml = FacturacionElectronicaHelper::generarXMLFactura($datosFactura, $config);

  echo json_encode([
    'status' => 1,
    'message' => 'XML generado correctamente',
    'data' => [
      'xml' => $xml,
      'clave_acceso' => $claveAcceso,
      'numero_comprobante' => "$establecimiento-$puntoEmision-$secuencial"
    ]
  ]);
} catch (Exception $e) {
  echo json_encode([
    'status' => 0,
    'message' => 'Error al generar XML: ' . $e->getMessage()
  ]);
}
