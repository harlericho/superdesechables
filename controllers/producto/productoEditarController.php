<?php
include_once '../../models/productoModel.php';

echo json_encode(ProductoModel::obtenerProductoId($_POST['id']));