<?php
include_once '../../models/cajachicaModel.php';
echo json_encode(CajaChicaModel::listarMovimientoCajachica());
