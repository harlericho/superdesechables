<?php
include_once '../../models/impuestoModel.php';

class ImpuestoController
{
  public function obtenerImpuestos()
  {
    $impuestos = ImpuestoModel::obtenerImpuestos();
    echo json_encode($impuestos);
  }

  public function obtenerImpuestoActivo()
  {
    try {
      $impuesto = ImpuestoModel::obtenerImpuestoActivo();

      if ($impuesto) {
        echo json_encode($impuesto);
      } else {
        // Si no hay impuesto activo, devolver un mensaje de debug
        echo json_encode([
          'error' => 'No hay impuesto activo configurado',
          'impuesto_porcentaje' => 0,
          'impuesto_nombre' => 'Sin configurar'
        ]);
      }
    } catch (Exception $e) {
      echo json_encode([
        'error' => 'Error en base de datos: ' . $e->getMessage(),
        'impuesto_porcentaje' => 0,
        'impuesto_nombre' => 'Error BD'
      ]);
    }
  }

  public function activarImpuesto()
  {
    $impuesto_id = $_POST['impuesto_id'];
    $response = ImpuestoModel::activarImpuesto($impuesto_id);

    if ($response) {
      echo json_encode(['status' => 'success', 'message' => 'Impuesto activado correctamente']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Error al activar impuesto']);
    }
  }

  public function guardarImpuesto()
  {
    $nombre = $_POST['nombre'];
    $porcentaje = $_POST['porcentaje'];

    $response = ImpuestoModel::guardarImpuesto($nombre, $porcentaje);

    if ($response) {
      echo json_encode(['status' => 'success', 'message' => 'Impuesto guardado correctamente']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Error al guardar impuesto']);
    }
  }

  public function editarImpuesto()
  {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $porcentaje = $_POST['porcentaje'];

    $response = ImpuestoModel::editarImpuesto($id, $nombre, $porcentaje);

    if ($response) {
      echo json_encode(['status' => 'success', 'message' => 'Impuesto actualizado correctamente']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Error al actualizar impuesto']);
    }
  }

  public function eliminarImpuesto()
  {
    $id = $_POST['id'];

    $response = ImpuestoModel::eliminarImpuesto($id);

    if ($response) {
      echo json_encode(['status' => 'success', 'message' => 'Impuesto eliminado correctamente']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Error al eliminar impuesto']);
    }
  }
}

// Manejo de solicitudes
if (isset($_GET['action'])) {
  $controller = new ImpuestoController();
  $action = $_GET['action'];

  if (method_exists($controller, $action)) {
    $controller->$action();
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
  }
}
