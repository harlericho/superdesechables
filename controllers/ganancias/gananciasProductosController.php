<?php
include_once '../../models/gananciasModel.php';

$productos = GananciasModel::obtenerProductosParaFiltro();

echo json_encode($productos);
