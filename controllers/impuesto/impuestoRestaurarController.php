<?php
include "../../config/db.php";
include "../../models/impuestoModel.php";

// Establecer headers para JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode([
    'status' => 'error',
    'message' => 'Método no permitido'
  ]);
  exit;
}

try {
  // Verificar que se recibió el ID del impuesto
  if (!isset($_POST['impuesto_id']) || empty($_POST['impuesto_id'])) {
    echo json_encode([
      'status' => 'error',
      'message' => 'ID del impuesto requerido'
    ]);
    exit;
  }

  $impuesto_id = intval($_POST['impuesto_id']);

  // Validar que el ID sea válido
  if ($impuesto_id <= 0) {
    echo json_encode([
      'status' => 'error',
      'message' => 'ID del impuesto inválido'
    ]);
    exit;
  }

  // Crear instancia del modelo
  $impuestoModel = new ImpuestoModel();

  // Restaurar el impuesto (cambiar estado a 1)
  $resultado = $impuestoModel->restaurarImpuesto($impuesto_id);

  if ($resultado) {
    echo json_encode([
      'status' => 'success',
      'message' => 'Impuesto restaurado correctamente'
    ]);
  } else {
    echo json_encode([
      'status' => 'error',
      'message' => 'No se pudo restaurar el impuesto'
    ]);
  }
} catch (Exception $e) {
  error_log("Error en impuestoRestaurarController: " . $e->getMessage());
  echo json_encode([
    'status' => 'error',
    'message' => 'Error interno del servidor'
  ]);
}
