<?php
include_once '../../models/comprobanteModel.php';

echo json_encode(ComprobanteModel::obtenerTipoComprobante());