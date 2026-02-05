<?php
include_once '../../models/impuestoModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];

  try {
    if (empty($id)) {
      echo json_encode(['status' => 'error', 'message' => 'ID requerido']);
      exit;
    }

    // Verificar que el impuesto existe
    $conexion = Db::dbConnection();
    $sql = "SELECT * FROM tbl_impuesto WHERE impuesto_id = :id AND impuesto_estado = 1";
    $query = $conexion->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $impuesto = $query->fetch(PDO::FETCH_ASSOC);

    if (!$impuesto) {
      echo json_encode(['status' => 'error', 'message' => 'Impuesto no encontrado']);
      exit;
    }

    // Verificar si es el impuesto actualmente en uso
    if ($impuesto['impuesto_activo'] == 1) {
      echo json_encode(['status' => 'error', 'message' => 'No puedes eliminar el impuesto que está actualmente en uso']);
      exit;
    }

    // Eliminar el impuesto (eliminación lógica)
    $resultado = ImpuestoModel::eliminarImpuesto($id);

    if ($resultado) {
      echo json_encode(['status' => 'success', 'message' => 'Impuesto eliminado correctamente']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el impuesto']);
    }
  } catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $e->getMessage()]);
  }
} else {
  echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
