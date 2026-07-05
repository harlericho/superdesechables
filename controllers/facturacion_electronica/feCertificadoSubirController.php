<?php

/**
 * Controlador: Subir certificado digital .p12
 */

require_once '../../config/db.php';
require_once '../../config/encryption.php';
include_once '../../models/facturacionElectronicaModel.php';

header('Content-Type: application/json');

try {
  // Verificar que se subió un archivo
  if (!isset($_FILES['certificado_archivo']) || $_FILES['certificado_archivo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
      'status' => 0,
      'message' => 'No se recibió el archivo del certificado'
    ]);
    exit;
  }

  // Verificar que se proporcionó la contraseña
  if (empty($_POST['config_fe_certificado_password'])) {
    echo json_encode([
      'status' => 0,
      'message' => 'La contraseña del certificado es requerida'
    ]);
    exit;
  }

  $archivo = $_FILES['certificado_archivo'];
  $password = $_POST['config_fe_certificado_password'];

  // Validar extensión
  $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
  if (!in_array($extension, ['p12', 'pfx'])) {
    echo json_encode([
      'status' => 0,
      'message' => 'El archivo debe ser .p12 o .pfx'
    ]);
    exit;
  }

  // Leer el contenido del archivo
  $certificadoBlob = file_get_contents($archivo['tmp_name']);

  // Validar que se puede leer el certificado con la contraseña proporcionada
  $certs = [];
  if (!openssl_pkcs12_read($certificadoBlob, $certs, $password)) {
    echo json_encode([
      'status' => 0,
      'message' => 'No se pudo leer el certificado. Verifique la contraseña.'
    ]);
    exit;
  }

  // Obtener información del certificado
  $certInfo = openssl_x509_parse($certs['cert']);
  $validoHasta = date('Y-m-d', $certInfo['validTo_time_t']);

  // Verificar que el certificado no esté vencido
  if ($certInfo['validTo_time_t'] < time()) {
    echo json_encode([
      'status' => 0,
      'message' => 'El certificado está vencido. Fecha de vencimiento: ' . $validoHasta
    ]);
    exit;
  }

  // Obtener configuración actual (sin importar si está activa)
  $config = FacturacionElectronicaModel::obtenerConfiguracion();
  if (!$config) {
    echo json_encode([
      'status' => 0,
      'message' => 'Primero debe guardar los datos del emisor'
    ]);
    exit;
  }

  $configId = $config['config_fe_id'];

  // Encriptar la contraseña del certificado
  $passwordEncriptado = Encryption::_encryptacion($password);

  // Subir el certificado con contraseña y fecha de caducidad
  if (FacturacionElectronicaModel::subirCertificado($configId, $certificadoBlob, $archivo['name'], $passwordEncriptado, $validoHasta)) {
    echo json_encode([
      'status' => 1,
      'message' => 'Certificado cargado correctamente',
      'data' => [
        'nombre_archivo' => $archivo['name'],
        'fecha_caducidad' => $validoHasta,
        'emisor' => $certInfo['subject']['CN'] ?? 'No disponible'
      ]
    ]);
  } else {
    echo json_encode([
      'status' => 0,
      'message' => 'Error al guardar el certificado en la base de datos'
    ]);
  }
} catch (Exception $e) {
  echo json_encode([
    'status' => 0,
    'message' => 'Error: ' . $e->getMessage()
  ]);
}
