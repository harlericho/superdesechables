<?php
include_once '../../models/impuestoModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $porcentaje = $_POST['porcentaje'];
  $activo = isset($_POST['activo']) ? $_POST['activo'] : 0;

  try {
    // Validaciones
    if (empty($nombre) || empty($porcentaje)) {
      echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
      exit;
    }

    if ($porcentaje < 0 || $porcentaje > 100) {
      echo json_encode(['status' => 'error', 'message' => 'El porcentaje debe estar entre 0 y 100']);
      exit;
    }

    // Verificar si ya existe un impuesto con el mismo nombre
    $impuestos = ImpuestoModel::obtenerImpuestos();
    foreach ($impuestos as $impuesto) {
      if (strtoupper($impuesto['impuesto_nombre']) === strtoupper($nombre)) {
        echo json_encode(['status' => 'error', 'message' => 'Ya existe un impuesto con ese nombre']);
        exit;
      }
    }

    // Guardar el impuesto
    $resultado = ImpuestoModel::guardarImpuesto($nombre, $porcentaje);

    if ($resultado) {
      // Si se marcó como activo, activarlo después de guardarlo
      if ($activo == 1) {
        $conexion = Db::dbConnection();
        $sql = "SELECT impuesto_id FROM tbl_impuesto WHERE impuesto_nombre = :nombre ORDER BY impuesto_id DESC LIMIT 1";
        $query = $conexion->prepare($sql);
        $query->bindParam(':nombre', $nombre);
        $query->execute();
        $nuevo_impuesto = $query->fetch(PDO::FETCH_ASSOC);

        if ($nuevo_impuesto) {
          ImpuestoModel::activarImpuesto($nuevo_impuesto['impuesto_id']);
        }
      }

      echo json_encode(['status' => 'success', 'message' => 'Impuesto guardado correctamente']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Error al guardar el impuesto']);
    }
  } catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $e->getMessage()]);
  }
} else {
  echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
