<?php

/**
 * Controlador: Guardar configuración de FE
 */

require_once '../../config/db.php';
require_once '../../config/encryption.php';
include_once '../../models/facturacionElectronicaModel.php';

header('Content-Type: application/json');

try {
  // Validar campos requeridos
  $camposRequeridos = ['config_fe_ruc', 'config_fe_razon_social', 'config_fe_direccion_matriz'];

  foreach ($camposRequeridos as $campo) {
    if (empty($_POST[$campo])) {
      echo json_encode([
        'status' => 0,
        'message' => 'El campo ' . $campo . ' es requerido'
      ]);
      exit;
    }
  }

  // Validar RUC (13 dígitos)
  if (!preg_match('/^\d{13}$/', $_POST['config_fe_ruc'])) {
    echo json_encode([
      'status' => 0,
      'message' => 'El RUC debe tener 13 dígitos'
    ]);
    exit;
  }

  // Preparar datos
  $data = [
    'config_fe_id' => $_POST['config_fe_id'] ?? 1,
    'config_fe_ruc' => $_POST['config_fe_ruc'],
    'config_fe_razon_social' => $_POST['config_fe_razon_social'],
    'config_fe_nombre_comercial' => $_POST['config_fe_nombre_comercial'] ?? '',
    'config_fe_direccion_matriz' => $_POST['config_fe_direccion_matriz'],
    'config_fe_direccion_sucursal' => $_POST['config_fe_direccion_sucursal'] ?? '',
    'config_fe_obligado_contabilidad' => $_POST['config_fe_obligado_contabilidad'] ?? 'NO',
    'config_fe_regimen_microempresa' => $_POST['config_fe_regimen_microempresa'] ?? 'NO',
    'config_fe_agente_retencion' => $_POST['config_fe_agente_retencion'] ?? 'NO',
    'config_fe_contribuyente_rimpe' => $_POST['config_fe_contribuyente_rimpe'] ?? 'NO',
    'config_fe_certificado_password' => '',
    'config_fe_certificado_fecha_caducidad' => $_POST['config_fe_certificado_fecha_caducidad'] ?? null,
    'config_fe_ambiente' => $_POST['config_fe_ambiente'] ?? 'PRUEBAS',
    'config_fe_tipo_emision' => $_POST['config_fe_tipo_emision'] ?? 'NORMAL',
    'config_fe_email_envio' => $_POST['config_fe_email_envio'] ?? '',
    'config_fe_email_copia' => $_POST['config_fe_email_copia'] ?? '',
    'config_fe_enviar_email_automatico' => isset($_POST['config_fe_enviar_email_automatico']) ? 1 : 0,
    'config_fe_activo' => isset($_POST['config_fe_activo']) ? 1 : 0
  ];

  // Guardar
  if (FacturacionElectronicaModel::guardarConfiguracion($data)) {
    echo json_encode([
      'status' => 1,
      'message' => 'Configuración guardada correctamente'
    ]);
  } else {
    echo json_encode([
      'status' => 0,
      'message' => 'Error al guardar la configuración'
    ]);
  }
} catch (Exception $e) {
  echo json_encode([
    'status' => 0,
    'message' => 'Error: ' . $e->getMessage()
  ]);
}
