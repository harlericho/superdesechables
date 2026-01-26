<?php
session_start();
include_once '../../models/usuarioModel.php';
include_once '../../config/encryption.php';
include_once '../../config/session.php';

$arrayName = array(
    'email' => $_POST['email'],
    'password' => Encryption::_encryptacion($_POST['password']),
);

if (UsuarioModel::existeUsuarioEmailPassword($arrayName)) {
    if (UsuarioModel::existeUsuarioEmailPasswordInactivo($arrayName)) {
        echo 3;
    } else {
        $_SESSION['email'] = $_POST['email'];
        // Establecer timestamp de inicio de sesión
        SessionManager::setLoginTimestamp();
        echo 1;
    }
} else {
    echo 2;
}
