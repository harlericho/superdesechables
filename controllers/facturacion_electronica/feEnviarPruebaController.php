<?php

/**
 * Controlador: Enviar factura de prueba al SRI
 */

require_once '../../config/db.php';
require_once '../../config/encryption.php';
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

  // Verificar certificado
  if (empty($config['config_fe_certificado_digital'])) {
    echo json_encode([
      'status' => 0,
      'message' => 'No hay certificado cargado'
    ]);
    exit;
  }

  // Generar datos de prueba
  $fecha = date('dmY');
  $tipoComprobante = '01';
  $ruc = $config['config_fe_ruc'];
  $ambiente = $config['config_fe_ambiente'] == 'PRUEBAS' ? '1' : '2';
  $establecimiento = str_pad($config['config_fe_cod_establecimiento'], 3, '0', STR_PAD_LEFT);
  $puntoEmision = str_pad($config['config_fe_cod_punto_emision'], 3, '0', STR_PAD_LEFT);
  $serie = $establecimiento . $puntoEmision;
  $secuencial = str_pad(1, 9, '0', STR_PAD_LEFT);
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
    'fe_iva_valor' => 1.50,
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
        'codigo_porcentaje_iva' => '4',
        'tarifa_iva' => 15,
        'valor_iva' => 1.50
      ]
    ],
    'informacion_adicional' => [
      'Email' => 'prueba@ejemplo.com'
    ]
  ];

  // 1. Generar XML
  $xml = FacturacionElectronicaHelper::generarXMLFactura($datosFactura, $config);

  // 2. Firmar XML
  $password = Encryption::_desencryptacion($config['config_fe_certificado_password']);
  $xmlFirmado = FacturacionElectronicaHelper::firmarXML(
    $xml,
    $config['config_fe_certificado_digital'],
    $password
  );

  if (!$xmlFirmado) {
    echo json_encode([
      'status' => 0,
      'message' => 'Error al firmar el XML de prueba'
    ]);
    exit;
  }

  // 3. Enviar al SRI (simulado)
  // NOTA: Aquí deberías implementar la integración real con SOAP del SRI
  // Por ahora, retornamos éxito simulado

  echo json_encode([
    'status' => 1,
    'message' => 'Proceso de prueba completado',
    'data' => [
      'clave_acceso' => $claveAcceso,
      'numero_comprobante' => "$establecimiento-$puntoEmision-$secuencial",
      'estado_sri' => 'SIMULADO - AUTORIZADO',
      'numero_autorizacion' => $claveAcceso,
      'fecha_autorizacion' => date('d/m/Y H:i:s'),
      'mensaje_sri' => 'XML generado y firmado correctamente. Integración con SRI pendiente.',
      'detalles' => 'Para producción debes implementar la conexión SOAP con los WebServices del SRI'
    ]
  ]);
} catch (Exception $e) {
  echo json_encode([
    'status' => 0,
    'message' => 'Error en prueba: ' . $e->getMessage()
  ]);
}
