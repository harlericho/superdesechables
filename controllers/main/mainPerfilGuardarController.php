<?php
include_once '../../models/usuarioModel.php';
session_start();
echo json_encode(UsuarioModel::actualizarUsuarioDatos($_POST["usuario_nombres"], $_SESSION['email']));
