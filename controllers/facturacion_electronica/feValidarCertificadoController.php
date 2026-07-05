<?php

/**
 * Controlador: Validar certificado digital
 */

require_once '../../config/db.php';
require_once '../../config/encryption.php';
include_once '../../models/facturacionElectronicaModel.php';

header('Content-Type: application/json');

try {
  // Obtener configuración actual
  $config = FacturacionElectronicaModel::obtenerConfiguracion();

  if (!$config) {
    echo json_encode([
      'status' => 0,
      'message' => 'No hay configuración disponible'
    ]);
    exit;
  }

  // Verificar que exista certificado
  if (empty($config['config_fe_certificado_digital'])) {
    echo json_encode([
      'status' => 0,
      'message' => 'No hay certificado cargado'
    ]);
    exit;
  }

  // Verificar que exista contraseña
  if (empty($config['config_fe_certificado_password'])) {
    echo json_encode([
      'status' => 0,
      'message' => 'No se encontró la contraseña del certificado'
    ]);
    exit;
  }

  // Desencriptar contraseña
  $password = Encryption::_desencryptacion($config['config_fe_certificado_password']);

  // Leer certificado
  $certs = [];
  if (!openssl_pkcs12_read($config['config_fe_certificado_digital'], $certs, $password)) {
    echo json_encode([
      'status' => 0,
      'message' => 'No se pudo leer el certificado. La contraseña puede ser incorrecta.'
    ]);
    exit;
  }

  // Obtener información del certificado
  $certInfo = openssl_x509_parse($certs['cert']);

  // Fechas
  $validoDesde = date('Y-m-d', $certInfo['validFrom_time_t']);
  $validoHasta = date('Y-m-d', $certInfo['validTo_time_t']);
  $ahora = time();
  $diasRestantes = floor(($certInfo['validTo_time_t'] - $ahora) / 86400);

  // Verificar si está vencido
  if ($certInfo['validTo_time_t'] < $ahora) {
    echo json_encode([
      'status' => 0,
      'message' => 'El certificado está vencido',
      'data' => [
        'emisor' => $certInfo['subject']['CN'] ?? 'No disponible',
        'valido_desde' => $validoDesde,
        'valido_hasta' => $validoHasta,
        'dias_restantes' => $diasRestantes,
        'vencido' => true
      ]
    ]);
    exit;
  }

  // Verificar si está por vencer (menos de 30 días)
  $advertencia = '';
  if ($diasRestantes < 30) {
    $advertencia = 'ADVERTENCIA: El certificado vencerá en menos de 30 días. ';
  }

  echo json_encode([
    'status' => 1,
    'message' => $advertencia . 'Certificado válido',
    'data' => [
      'emisor' => $certInfo['subject']['CN'] ?? 'No disponible',
      'ruc' => $certInfo['subject']['serialNumber'] ?? 'No disponible',
      'valido_desde' => $validoDesde,
      'valido_hasta' => $validoHasta,
      'dias_restantes' => $diasRestantes,
      'vencido' => false,
      'por_vencer' => $diasRestantes < 30
    ]
  ]);
} catch (Exception $e) {
  echo json_encode([
    'status' => 0,
    'message' => 'Error al validar certificado: ' . $e->getMessage()
  ]);
}
