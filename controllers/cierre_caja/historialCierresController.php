<?php
session_start();
date_default_timezone_set('America/Guayaquil');
include_once '../../models/cierreCajaModel.php';

// Verificar sesión activa
if (!isset($_SESSION['email'])) {
  echo json_encode(['error' => 'Sesión no válida']);
  exit;
}

try {
  $limite = isset($_POST['limite']) ? intval($_POST['limite']) : 30;

  $historial = CierreCajaModel::obtenerHistorialCierres($limite);

  if ($historial) {
    $response = [
      'success' => true,
      'historial' => $historial
    ];
  } else {
    $response = [
      'success' => true,
      'historial' => [],
      'message' => 'No se encontraron cierres de caja'
    ];
  }

  echo json_encode($response);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Error al obtener historial: ' . $e->getMessage()
  ]);
}
