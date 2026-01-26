<?php
include_once '../../models/configserieModel.php';

$arrayName = array(
  'primera_serie' => $_POST['primera_serie'],
  'segunda_serie' => $_POST['segunda_serie'],
  'secuencial' => $_POST['secuencial']
  // 'secuencial' =>  str_pad($_POST['secuencial'], 9, "0", STR_PAD_LEFT)
);
if (ConfigserieModel::guardarConfigSerie($arrayName)) {
  echo 1;
} else {
  echo 0;
}
