<?php
include_once '../../models/usuarioModel.php';
include_once '../../config/encryption.php';
$arrayName = array(
    'nombres' => strtoupper($_POST['nombres']),
    'email' => strtolower($_POST['email']),
    'password' => Encryption::_encryptacion($_POST['password']),
    'rol' => $_POST['rol'],
    'id' => $_POST['id']
);

if (UsuarioModel::existeUsuarioEmailActualizar(strtolower($_POST['email']), $_POST['id'])['COUNT(*)'] >= 2) {
    echo 2;
} else {
    if (UsuarioModel::actualizarUsuario($arrayName)) {
        echo 1;
    } else {
        echo 0;
    }
}
