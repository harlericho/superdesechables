<?php
include_once '../../models/usuarioModel.php';
include_once '../../config/encryption.php';
$arrayName = array(
    'nombres' => strtoupper($_POST['nombres']),
    'email' => strtolower($_POST['email']),
    'password' => Encryption::_encryptacion($_POST['password']),
    'rol' => $_POST['rol']
);

if (UsuarioModel::existeUsuarioEmail(strtolower($_POST['email']))) {
    echo 2;
} else {
    if (UsuarioModel::guardarUsuario($arrayName)) {
        echo 1;
    } else {
        echo 0;
    }
}
