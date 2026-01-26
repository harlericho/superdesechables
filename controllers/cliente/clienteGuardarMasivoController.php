<?php
include_once  '../../models/clienteModel.php';
include_once '../../config/csvProcessor.php';
if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
  $fileTmpPath = $_FILES['file']['tmp_name'];
  $csvProcessor = new CSVProcessor();
  $data = $csvProcessor->procesarCSV($fileTmpPath);

  if ($data === false) {
    echo json_encode(["status" => "0", "message" => "Error al procesar el archivo CSV."]);
    return;
  }

  $existentes = [];
  $guardados = 0;
  $errores = [];

  foreach ($data as $index => $row) {
    $dni = isset($row['dni']) ? trim($row['dni']) : '';
    $email = isset($row['email']) ? trim($row['email']) : '';
    $telefono = isset($row['telefono']) ? trim($row['telefono']) : '';
    $nombres = isset($row['nombres']) ? strtoupper(trim($row['nombres'])) : '';
    $apellidos = isset($row['apellidos']) ? strtoupper(trim($row['apellidos'])) : '';
    $direccion = isset($row['direccion']) ? strtoupper(trim($row['direccion'])) : '';
    $rol = isset($row['rol']) ? trim($row['rol']) : '';

    // Ignora filas vacías o sin datos principales
    if ($dni === '' && $email === '' && $telefono === '') {
      continue;
    }

    if (ClienteModel::existeClienteDni($dni)) {
      $existentes[] = "DNI '" . $dni . "'";
      continue;
    } elseif (ClienteModel::existeClienteEmail($email)) {
      $existentes[] = "Email '" . $email . "'";
      continue;
    } elseif (ClienteModel::existeClienteTelefono($telefono)) {
      $existentes[] = "Teléfono '" . $telefono . "'";
      continue;
    } else {
      $arrayName = array(
        'dni' => $dni,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'direccion' => $direccion,
        'telefono' => $telefono,
        'email' => strtolower($email),
        'rol' => $rol
      );

      $result = ClienteModel::guardarCliente($arrayName);
      if ($result === false) {
        $errores[] = $dni;
      } else {
        $guardados++;
      }
    }
  }

  if (!empty($errores)) {
    echo json_encode(["status" => "0", "message" => "Error al guardar los siguientes clientes: " . implode(", ", $errores)]);
  } elseif (!empty($existentes)) {
    echo json_encode(["status" => "2", "message" => "Algunos clientes ya existen: " . implode(", ", $existentes) . ". Se guardaron $guardados nuevos clientes."]);
  } else {
    echo json_encode(["status" => "1", "message" => "Clientes guardados correctamente: $guardados"]);
  }
} else {
  echo json_encode(["status" => "0", "message" => "Error al cargar el archivo."]);
}
