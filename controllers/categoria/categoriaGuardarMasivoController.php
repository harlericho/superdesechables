<?php
include_once  '../../models/categoriaModel.php';
include_once '../../config/csvProcessor.php';
if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
  $fileTmpPath = $_FILES['file']['tmp_name'];
  $csvProcessor = new CSVProcessor();
  $data = $csvProcessor->procesarCSV($fileTmpPath);

  $existentes = [];
  $guardados = 0;
  $errores = [];

  foreach ($data as $index => $row) {
    $descripcion = isset($row['descripcion']) ? trim($row['descripcion']) : '';
    if ($descripcion === '') {
      continue; // Ignora filas vacías
    }
    if (CategoriaModel::existeCategoriaDescripcion($descripcion)) {
      $existentes[] = "'" . $descripcion . "'";
      continue;
    } else {
      $result = CategoriaModel::guardarCategoria($descripcion);
      if ($result === false) {
        $errores[] = $descripcion;
      } else {
        $guardados++;
      }
    }
  }

  if (!empty($errores)) {
    echo json_encode(["status" => "0", "message" => "Error al guardar las siguientes categorías: " . implode(", ", $errores)]);
  } elseif (!empty($existentes)) {
    echo json_encode(["status" => "2", "message" => "Algunas categorías ya existen: " . implode(", ", $existentes) . ". Se guardaron $guardados nuevas categorías."]);
  } else {
    echo json_encode(["status" => "1", "message" => "Categorías guardadas correctamente: $guardados"]);
  }
} else {
  echo json_encode(["status" => "0", "message" => "Error al cargar el archivo."]);
}
