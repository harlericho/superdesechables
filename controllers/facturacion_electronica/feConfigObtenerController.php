<?php

/**
 * Controlador: Obtener configuración de FE
 */

require_once '../../config/db.php';
include_once '../../models/facturacionElectronicaModel.php';

header('Content-Type: application/json');

try {
  $config = FacturacionElectronicaModel::obtenerConfiguracion();

  if ($config) {
    // No enviar la contraseña del certificado al frontend
    unset($config['config_fe_certificado_password']);
    unset($config['config_fe_certificado_digital']); // Muy grande para enviar

    echo json_encode([
      'status' => 1,
      'message' => 'Configuración obtenida',
      'data' => $config
    ]);
  } else {
    echo json_encode([
      'status' => 0,
      'message' => 'No hay configuración disponible',
      'data' => null
    ]);
  }
} catch (Exception $e) {
  echo json_encode([
    'status' => 0,
    'message' => 'Error al obtener configuración: ' . $e->getMessage()
  ]);
}
