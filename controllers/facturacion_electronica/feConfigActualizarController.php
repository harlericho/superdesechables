<?php

/**
 * Controlador: Actualizar configuración (ambiente, emails, etc.)
 */

require_once '../../config/db.php';
include_once '../../models/facturacionElectronicaModel.php';

header('Content-Type: application/json');

try {
  // Obtener configuración actual (sin importar si está activa)
  $config = FacturacionElectronicaModel::obtenerConfiguracion();

  if (!$config) {
    echo json_encode([
      'status' => 0,
      'message' => 'No hay configuración para actualizar'
    ]);
    exit;
  }

  // Actualizar solo los campos de configuración
  $data = [
    'config_fe_id' => $config['config_fe_id'],
    'config_fe_ruc' => $config['config_fe_ruc'],
    'config_fe_razon_social' => $config['config_fe_razon_social'],
    'config_fe_nombre_comercial' => $config['config_fe_nombre_comercial'],
    'config_fe_direccion_matriz' => $config['config_fe_direccion_matriz'],
    'config_fe_direccion_sucursal' => $config['config_fe_direccion_sucursal'],
    'config_fe_obligado_contabilidad' => $config['config_fe_obligado_contabilidad'],
    'config_fe_regimen_microempresa' => $config['config_fe_regimen_microempresa'],
    'config_fe_agente_retencion' => $config['config_fe_agente_retencion'],
    'config_fe_contribuyente_rimpe' => $config['config_fe_contribuyente_rimpe'],
    'config_fe_certificado_password' => $config['config_fe_certificado_password'],
    'config_fe_certificado_fecha_caducidad' => $config['config_fe_certificado_fecha_caducidad'],
    'config_fe_ambiente' => $_POST['config_fe_ambiente'] ?? $config['config_fe_ambiente'],
    'config_fe_tipo_emision' => $_POST['config_fe_tipo_emision'] ?? $config['config_fe_tipo_emision'],
    'config_fe_email_envio' => $_POST['config_fe_email_envio'] ?? $config['config_fe_email_envio'],
    'config_fe_email_copia' => $_POST['config_fe_email_copia'] ?? $config['config_fe_email_copia'],
    'config_fe_enviar_email_automatico' => isset($_POST['config_fe_enviar_email_automatico']) ? 1 : 0,
    'config_fe_activo' => isset($_POST['config_fe_activo']) ? 1 : 0,
    'config_fe_permitir_ventas_simples' => isset($_POST['config_fe_permitir_ventas_simples']) ? 1 : 0
  ];

  // Validar que si se activa FE, esté todo configurado
  if ($data['config_fe_activo'] == 1) {
    // Verificar que exista certificado
    if (empty($config['config_fe_certificado_digital'])) {
      echo json_encode([
        'status' => 0,
        'message' => 'No se puede activar la FE sin un certificado digital cargado'
      ]);
      exit;
    }

    // Verificar que la contraseña del certificado esté guardada
    if (empty($config['config_fe_certificado_password'])) {
      echo json_encode([
        'status' => 0,
        'message' => 'No se puede activar la FE sin la contraseña del certificado'
      ]);
      exit;
    }
  }

  if (FacturacionElectronicaModel::guardarConfiguracion($data)) {
    $mensaje = 'Configuración actualizada correctamente';

    if ($data['config_fe_activo'] == 1) {
      $mensaje .= '. La facturación electrónica está ACTIVA';
    } else {
      $mensaje .= '. La facturación electrónica está INACTIVA';
    }

    echo json_encode([
      'status' => 1,
      'message' => $mensaje
    ]);
  } else {
    echo json_encode([
      'status' => 0,
      'message' => 'Error al actualizar la configuración'
    ]);
  }
} catch (Exception $e) {
  echo json_encode([
    'status' => 0,
    'message' => 'Error: ' . $e->getMessage()
  ]);
}
