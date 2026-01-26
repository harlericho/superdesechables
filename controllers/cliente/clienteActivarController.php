<?php
include_once  '../../models/clienteModel.php';

echo ClienteModel::activarCliente($_POST['id']);