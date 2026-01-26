<?php
include_once  '../../models/clienteModel.php';

$arrayName = array(
    'dni' => $_POST['dni'],
    'nombres' => strtoupper($_POST['nombres']),
    'apellidos' => strtoupper($_POST['apellidos']),
    'direccion' => strtoupper($_POST['direccion']),
    'telefono' => $_POST['telefono'],
    'email' => strtolower($_POST['email']),
    'rol' => $_POST['rol'],
    'id' => $_POST['id']
);

if (ClienteModel::existeClienteDniActulizar($_POST['dni'], $_POST['id'])['COUNT(*)'] >= 2) {
    echo 2;
} else if (ClienteModel::existeClienteEmailActualizar(strtolower($_POST['email']), $_POST['id'])['COUNT(*)'] >= 2) {
    echo 3;
} else if (ClienteModel::existeClienteTelefonoActualizar($_POST['telefono'], $_POST['id'])['COUNT(*)'] >= 2) {
    echo 4;
} else {
    if (ClienteModel::actualizarCliente($arrayName)) {
        echo 1;
    } else {
        echo 0;
    }
}
