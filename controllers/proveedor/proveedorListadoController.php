<?php
include_once '../../models/proveedorModel.php';

echo json_encode(ProveedorModel::obtenerProveedores());