<?php
include_once  '../../models/clienteModel.php';

echo ClienteModel::inactivarCliente($_POST['id']);
