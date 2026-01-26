<?php
include_once  '../../models/clienteModel.php';
echo json_encode(ClienteModel::obtenerClientes());
