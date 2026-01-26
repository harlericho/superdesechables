<?php
include_once '../../models/cajachicaModel.php';

$arrayName = array(
  'mov_fecharegistro' => $_POST['fecha'],
  'mov_descripcion' => strtoupper($_POST['descripcion']),
  'mov_tipo' => $_POST['tipo'],
  'mov_monto' => $_POST['monto']
);
if (CajaChicaModel::guardarMovimientoCajachica($arrayName)) {
  echo 1;
} else {
  echo 0;
}
