<?php
include_once '../../models/facturaModel.php';
echo json_encode(FacturaModel::obtenerFacturasInactivasGeneral());