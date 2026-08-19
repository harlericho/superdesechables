<?php
include_once '../../models/tempModel.php';

if (TempModel::eliminarDatosTemp()) {
  echo json_encode(1);
} else {
  echo json_encode(0);
}
