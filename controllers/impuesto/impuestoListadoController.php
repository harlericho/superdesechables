<?php
include_once '../../models/impuestoModel.php';

// Obtener todos los impuestos para mostrar en la tabla
$impuestos = ImpuestoModel::obtenerImpuestos();

if ($impuestos) {
  echo json_encode($impuestos);
} else {
  echo json_encode([]);
}
