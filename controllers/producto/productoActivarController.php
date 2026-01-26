<?php
include_once  '../../models/productoModel.php';

echo ProductoModel::activarProducto($_POST['id']);