<?php
include_once '../../models/impuestoModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $impuesto_id = $_POST['impuesto_id'];

  try {
    if (empty($impuesto_id)) {
      echo json_encode(['status' => 'error', 'message' => 'ID de impuesto requerido']);
      exit;
    }

    // Verificar que el impuesto existe y está activo
    $conexion = Db::dbConnection();
    $sql = "SELECT * FROM tbl_impuesto WHERE impuesto_id = :id AND impuesto_estado = 1";
    $query = $conexion->prepare($sql);
    $query->bindParam(':id', $impuesto_id, PDO::PARAM_INT);
    $query->execute();
    $impuesto = $query->fetch(PDO::FETCH_ASSOC);

    if (!$impuesto) {
      echo json_encode(['status' => 'error', 'message' => 'Impuesto no encontrado o ya eliminado']);
      exit;
    }

    // Activar el impuesto (esto desactivará automáticamente los demás)
    $resultado = ImpuestoModel::activarImpuesto($impuesto_id);

    if ($resultado) {
      echo json_encode([
        'status' => 'success',
        'message' => 'Impuesto "' . $impuesto['impuesto_nombre'] . '" (' . $impuesto['impuesto_porcentaje'] . '%) activado correctamente'
      ]);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Error al activar el impuesto']);
    }
  } catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $e->getMessage()]);
  }
} else {
  echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
