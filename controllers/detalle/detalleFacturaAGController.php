<?php
include_once '../../models/detalleModel.php';
echo json_encode(DetalleModel::obtenerDetalleProductoActivoGeneral());