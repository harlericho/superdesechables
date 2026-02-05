<?php
include_once '../../models/impuestoModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];

  try {
    if (empty($id)) {
      echo json_encode(['status' => 'error', 'message' => 'ID requerido']);
      exit;
    }

    // Obtener impuesto específico por ID
    $conexion = Db::dbConnection();
    $sql = "SELECT * FROM tbl_impuesto WHERE impuesto_id = :id AND impuesto_estado = 1";
    $query = $conexion->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $impuesto = $query->fetch(PDO::FETCH_ASSOC);

    if ($impuesto) {
      echo json_encode($impuesto);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Impuesto no encontrado']);
    }
  } catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $e->getMessage()]);
  }
} else {
  echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
