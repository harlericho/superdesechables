<?php
include_once '../../models/usuarioModel.php';
session_start();
echo json_encode(UsuarioModel::obtenerDatoUsuario($_SESSION["email"]));
