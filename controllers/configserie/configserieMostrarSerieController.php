<?php
include_once '../../models/configserieModel.php';

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'FACTURA';

$data = json_encode(ConfigserieModel::listarConfigSerie());
$serie = json_decode($data, true);

if ($tipo === 'TICKET') {
    $secuencial_ticket = isset($serie[0]['config_secuencial_ticket']) ? $serie[0]['config_secuencial_ticket'] : 1;
    $serie_formateada = 'TK-' . str_pad($secuencial_ticket, 7, "0", STR_PAD_LEFT);
} else {
    $primera_serie = $serie[0]['config_primera_serie'];
    $segunda_serie = $serie[0]['config_segunda_serie'];
    $secuencial = $serie[0]['config_secuencial'];
    $serie_formateada = $primera_serie . '-' . $segunda_serie . '-' . str_pad($secuencial, 9, "0", STR_PAD_LEFT);
}

echo json_encode($serie_formateada);
