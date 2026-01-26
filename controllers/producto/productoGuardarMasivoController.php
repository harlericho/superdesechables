<?php
include_once  '../../models/productoModel.php';
include_once  '../../models/categoriaModel.php';
include_once  '../../models/proveedorModel.php';
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
  $advertencias = [];

  foreach ($data as $index => $row) {
    // Validar que existan las columnas necesarias
    $codigo = isset($row['codigo']) ? trim($row['codigo']) : '';
    $nombre = isset($row['nombre']) ? trim($row['nombre']) : '';
    $descripcion = isset($row['descripcion']) ? trim($row['descripcion']) : '';
    $precio_compra = isset($row['precio_compra']) ? trim($row['precio_compra']) : '';
    $precio_venta = isset($row['precio_venta']) ? trim($row['precio_venta']) : '';
    $stock = isset($row['stock']) ? trim($row['stock']) : '';
    $imagen = isset($row['imagen']) ? trim($row['imagen']) : 'sin-imagen.png';
    $categoria = isset($row['categoria']) ? trim($row['categoria']) : '';
    $proveedor = isset($row['proveedor']) ? trim($row['proveedor']) : '';

    // Ignora filas vacías o sin datos principales
    if ($codigo === '' && $nombre === '') {
      continue;
    }

    // Validar campos obligatorios
    if ($codigo === '' || $nombre === '' || $precio_compra === '' || $precio_venta === '' || $stock === '') {
      $advertencias[] = "Fila con código '$codigo' - Faltan campos obligatorios";
      continue;
    }

    // Validar que los precios y stock sean numéricos
    if (!is_numeric($precio_compra) || !is_numeric($precio_venta) || !is_numeric($stock)) {
      $advertencias[] = "Fila con código '$codigo' - Precios o stock no son valores numéricos válidos";
      continue;
    }

    // Verificar si el producto ya existe
    if (ProductoModel::existeProductoCodigo($codigo)) {
      $existentes[] = "Código '" . $codigo . "'";
      continue;
    }

    // Obtener el ID de la categoría (acepta ID numérico o nombre)
    $categoria_id = null;
    if ($categoria !== '') {
      if (is_numeric($categoria)) {
        // Si es numérico, validar que el ID exista
        if (validarCategoriaId($categoria)) {
          $categoria_id = intval($categoria);
        } else {
          $advertencias[] = "Producto '$codigo' - Categoría con ID '$categoria' no existe";
          continue;
        }
      } else {
        // Si es texto, buscar por nombre
        $categoria_id = obtenerCategoriaIdPorNombre($categoria);
        if ($categoria_id === null) {
          $advertencias[] = "Producto '$codigo' - Categoría '$categoria' no existe";
          continue;
        }
      }
    }

    // Obtener el ID del proveedor (acepta ID numérico o DNI/RUC)
    $proveedor_id = null;
    if ($proveedor !== '') {
      if (is_numeric($proveedor) && strlen($proveedor) <= 5) {
        // Si es numérico corto, asumir que es un ID
        if (validarProveedorId($proveedor)) {
          $proveedor_id = intval($proveedor);
        } else {
          $advertencias[] = "Producto '$codigo' - Proveedor con ID '$proveedor' no existe";
          continue;
        }
      } else {
        // Si es texto largo o DNI/RUC, buscar por DNI
        $proveedor_id = obtenerProveedorIdPorDni($proveedor);
        if ($proveedor_id === null) {
          $advertencias[] = "Producto '$codigo' - Proveedor con DNI/RUC '$proveedor' no existe";
          continue;
        }
      }
    }

    // Preparar datos para guardar
    $arrayProducto = array(
      'codigo' => strtoupper($codigo),
      'nombre' => strtoupper($nombre),
      'descripcion' => strtoupper($descripcion),
      'precio_c' => $precio_compra,
      'precio' => $precio_venta,
      'stock' => intval($stock),
      'foto' => $imagen,
      'categoria_id' => $categoria_id,
      'proveedor_id' => $proveedor_id
    );

    $result = ProductoModel::guardarProducto($arrayProducto);
    if ($result === false) {
      $errores[] = "Código '$codigo'";
    } else {
      $guardados++;
    }
  }

  // Construir mensaje de respuesta
  $mensaje = "";
  $status = "1";

  if ($guardados > 0) {
    $mensaje .= "Productos guardados correctamente: $guardados. ";
  }

  if (!empty($existentes)) {
    $status = "2";
    $mensaje .= "Productos que ya existen: " . implode(", ", $existentes) . ". ";
  }

  if (!empty($advertencias)) {
    $status = "3";
    $mensaje .= "Advertencias: " . implode("; ", $advertencias) . ". ";
  }

  if (!empty($errores)) {
    $status = "0";
    $mensaje .= "Error al guardar productos: " . implode(", ", $errores) . ". ";
  }

  if ($guardados === 0 && empty($existentes)) {
    $status = "0";
    $mensaje = "No se guardó ningún producto. Verifique el formato del archivo.";
  }

  echo json_encode(["status" => $status, "message" => trim($mensaje)]);
} else {
  echo json_encode(["status" => "0", "message" => "Error al cargar el archivo."]);
}

/**
 * Función auxiliar para validar si existe una categoría por ID
 */
function validarCategoriaId($id)
{
  try {
    $sql = "SELECT categoria_id FROM tbl_categoria WHERE categoria_id = :id AND categoria_estado = 1";
    $query = Db::dbConnection()->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();
    return $query->rowCount() > 0;
  } catch (PDOException $e) {
    return false;
  }
}

/**
 * Función auxiliar para obtener el ID de una categoría por su descripción
 */
function obtenerCategoriaIdPorNombre($descripcion)
{
  try {
    $sql = "SELECT categoria_id FROM tbl_categoria WHERE categoria_descripcion = :descripcion AND categoria_estado = 1";
    $query = Db::dbConnection()->prepare($sql);
    $query->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['categoria_id'] : null;
  } catch (PDOException $e) {
    return null;
  }
}

/**
 * Función auxiliar para validar si existe un proveedor por ID
 */
function validarProveedorId($id)
{
  try {
    $sql = "SELECT proveedor_id FROM tbl_proveedor WHERE proveedor_id = :id AND proveedor_estado = 1";
    $query = Db::dbConnection()->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();
    return $query->rowCount() > 0;
  } catch (PDOException $e) {
    return false;
  }
}

/**
 * Función auxiliar para obtener el ID de un proveedor por su DNI/RUC
 */
function obtenerProveedorIdPorDni($dni)
{
  try {
    $sql = "SELECT proveedor_id FROM tbl_proveedor WHERE proveedor_dni = :dni AND proveedor_estado = 1";
    $query = Db::dbConnection()->prepare($sql);
    $query->bindParam(':dni', $dni, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['proveedor_id'] : null;
  } catch (PDOException $e) {
    return null;
  }
}
