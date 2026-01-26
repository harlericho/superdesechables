<?php
include_once  '../../models/productoModel.php';

echo ProductoModel::inactivarProducto($_POST['id']);
