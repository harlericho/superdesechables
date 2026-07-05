<?php

/**
 * Controlador: Probar conexión con el SRI
 */

require_once '../../config/db.php';
include_once '../../models/facturacionElectronicaModel.php';

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

  // URLs del SRI según ambiente
  $ambiente = $config['config_fe_ambiente'];

  if ($ambiente == 'PRUEBAS') {
    $urlRecepcion = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
    $urlAutorizacion = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
  } else {
    $urlRecepcion = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
    $urlAutorizacion = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
  }

  // Intentar conectar a la URL de recepción
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $urlRecepcion);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $error = curl_error($ch);
  curl_close($ch);

  if ($httpCode == 200 && strpos($response, 'wsdl') !== false) {
    echo json_encode([
      'status' => 1,
      'message' => 'Conexión exitosa con el SRI',
      'data' => [
        'ambiente' => $ambiente,
        'url_recepcion' => $urlRecepcion,
        'url_autorizacion' => $urlAutorizacion,
        'estado_servicio' => 'Disponible',
        'codigo_http' => $httpCode
      ]
    ]);
  } else {
    echo json_encode([
      'status' => 0,
      'message' => 'No se pudo conectar con el SRI',
      'data' => [
        'ambiente' => $ambiente,
        'url_recepcion' => $urlRecepcion,
        'codigo_http' => $httpCode,
        'error' => $error ?: 'No se recibió respuesta WSDL válida'
      ]
    ]);
  }
} catch (Exception $e) {
  echo json_encode([
    'status' => 0,
    'message' => 'Error al probar conexión: ' . $e->getMessage()
  ]);
}
