<?php
include_once '../../models/impuestoModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $nombre = $_POST['nombre'];
  $porcentaje = $_POST['porcentaje'];
  $activo = isset($_POST['activo']) ? $_POST['activo'] : 0;

  try {
    // Validaciones
    if (empty($id) || empty($nombre) || empty($porcentaje)) {
      echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
      exit;
    }

    if ($porcentaje < 0 || $porcentaje > 100) {
      echo json_encode(['status' => 'error', 'message' => 'El porcentaje debe estar entre 0 y 100']);
      exit;
    }

    // Verificar si ya existe otro impuesto con el mismo nombre
    $impuestos = ImpuestoModel::obtenerImpuestos();
    foreach ($impuestos as $impuesto) {
      if ($impuesto['impuesto_id'] != $id && strtoupper($impuesto['impuesto_nombre']) === strtoupper($nombre)) {
        echo json_encode(['status' => 'error', 'message' => 'Ya existe otro impuesto con ese nombre']);
        exit;
      }
    }

    // Editar el impuesto
    $resultado = ImpuestoModel::editarImpuesto($id, $nombre, $porcentaje);

    if ($resultado) {
      // Si se marcó como activo, activarlo
      if ($activo == 1) {
        ImpuestoModel::activarImpuesto($id);
      }

      echo json_encode(['status' => 'success', 'message' => 'Impuesto actualizado correctamente']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el impuesto']);
    }
  } catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $e->getMessage()]);
  }
} else {
  echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
