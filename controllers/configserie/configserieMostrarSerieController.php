<?php
include_once '../../models/configserieModel.php';
$data = json_encode(ConfigserieModel::listarConfigSerie());
// agrupuar la informaicon $data en 001-001-000000001
$serie = json_decode($data, true);
$primera_serie = $serie[0]['config_primera_serie'];
$segunda_serie = $serie[0]['config_segunda_serie'];
$secuencial = $serie[0]['config_secuencial'];
$serie = $primera_serie . '-' . $segunda_serie . '-' . str_pad($secuencial, 9, "0", STR_PAD_LEFT);
echo json_encode($serie);
